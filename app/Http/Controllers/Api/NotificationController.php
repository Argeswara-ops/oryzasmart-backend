<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    /**
     * Mendaftarkan / memperbarui FCM Token untuk pengguna yang sedang login.
     */
    public function updateToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string'],
        ]);

        $user = $request->user();
        $user->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM Token berhasil diperbarui',
            'data' => [
                'user_id' => $user->id,
                'fcm_token' => $user->fcm_token,
            ],
        ]);
    }
    /**
     * Menghapus FCM Token saat logout atau mematikan notifikasi.
     */
    public function removeToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'fcm_token' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM Token berhasil dihapus',
            'data' => null,
        ]);
    }

    /**
     * Endpoint untuk menguji pengiriman notifikasi dari backend ke perangkat.
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['nullable', 'string', 'max:500'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        // Non-admin users may only send test notifications to their own token
        $token = ($user->role === 'admin' && !empty($validated['fcm_token']))
            ? $validated['fcm_token']
            : $user->fcm_token;

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'FCM token tidak ditemukan pada akun Anda. Daftarkan token terlebih dahulu.',
            ], 422);
        }

        $title = $validated['title'] ?? 'Uji Coba Notifikasi Oryza Apps';
        $body = $validated['body'] ?? 'Koneksi Firebase Cloud Messaging dari backend Laravel berhasil!';
        $data = [
            'type' => 'TEST_NOTIFICATION',
            'timestamp' => (string) now()->timestamp,
        ];

        try {
            $sent = $this->firebaseService->sendToToken($token, $title, $body, $data);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notifikasi uji coba berhasil dikirim ke perangkat',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi via Firebase.',
            ], 500);
        } catch (\Throwable $e) {
            Log::error('FCM sendTest failure: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat mengirim notifikasi.',
            ], 500);
        }
    }
}
