<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected string $credentialsPath;
    protected ?array $credentials = null;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/firebase-credentials.json');
    }
    /**
     * Memuat file kredensial Service Account Firebase.
     */
    protected function getCredentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        if (! file_exists($this->credentialsPath)) {
            throw new \RuntimeException("Firebase credentials file not found at: {$this->credentialsPath}");
        }

        $content = file_get_contents($this->credentialsPath);
        $data = json_decode($content, true);

        if (! is_array($data) || empty($data['private_key']) || empty($data['client_email'])) {
            throw new \RuntimeException('Invalid Firebase credentials JSON format.');
        }

        $this->credentials = $data;

        return $this->credentials;
    }

    /**
     * Mendapatkan OAuth 2.0 Access Token dari Google via JWT Service Account.
     */
    public function getAccessToken(): string
    {
        return Cache::remember('firebase_oauth_access_token', 3500, function () {
            $creds = $this->getCredentials();

            $now = time();
            $header = $this->base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ]));

            $claim = $this->base64UrlEncode(json_encode([
                'iss' => $creds['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $jwtHeaderClaim = $header . '.' . $claim;
            $signature = '';

            $success = openssl_sign(
                $jwtHeaderClaim,
                $signature,
                $creds['private_key'],
                OPENSSL_ALGO_SHA256
            );

            if (! $success) {
                throw new \RuntimeException('Failed to sign JWT for Firebase OAuth2.');
            }

            $jwt = $jwtHeaderClaim . '.' . $this->base64UrlEncode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (! $response->successful()) {
                Log::error('Firebase OAuth Token error: ' . $response->body());
                throw new \RuntimeException('Failed to obtain Google OAuth access token: ' . $response->body());
            }

            $json = $response->json();

            return $json['access_token'];
        });
    }

    /**
     * Mengirim push notification ke single device via FCM Token.
     */
    public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $creds = $this->getCredentials();
        $projectId = $creds['project_id'] ?? 'oryzasmart-f2ad2';
        $accessToken = $this->getAccessToken();

        // Data payload wajib bernilai string di FCM HTTP v1
        $stringData = [];
        foreach ($data as $key => $val) {
            $stringData[(string) $key] = is_scalar($val) ? (string) $val : json_encode($val);
        }

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $stringData,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'channel_id' => 'high_importance_channel',
                        'sound' => 'default',
                        'default_vibrate_timings' => true,
                    ],
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

        if ($response->successful()) {
            Log::info("FCM Notification sent successfully to token ending with ...".substr($fcmToken, -8));
            return true;
        }

        Log::warning('FCM Notification send error: ' . $response->body());

        // Jika token sudah tidak aktif / expired, hapus dari database user
        if ($response->status() === 404 || str_contains($response->body(), 'UNREGISTERED')) {
            User::where('fcm_token', $fcmToken)->update(['fcm_token' => null]);
            Log::info("Stale FCM Token cleared for token ending with ...".substr($fcmToken, -8));
        }

        return false;
    }

    /**
     * Mengirim notifikasi ke semua user yang memiliki FCM token terdaftar.
     */
    public function broadcastToUsers(string $title, string $body, array $data = []): int
    {
        $users = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get();

        $successCount = 0;
        foreach ($users as $user) {
            if ($this->sendToToken($user->fcm_token, $title, $body, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Helper base64 url-safe encoder.
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
