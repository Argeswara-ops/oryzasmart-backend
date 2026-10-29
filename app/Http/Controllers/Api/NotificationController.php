<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $user = $request->user();
        $token = $request->input('fcm_token') ?? $user->fcm_token;

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'FCM token tidak ditemukan pada user atau request. Daftarkan token terlebih dahulu.',
            ], 422);
        }

        $title = $request->input('title', 'Uji Coba Notifikasi PadiApps');
        $body = $request->input('body', 'Koneksi Firebase Cloud Messaging dari backend Laravel berhasil!');
        $data = $request->input('data', [
            'type' => 'TEST_NOTIFICATION',
            'timestamp' => (string) now()->timestamp,
        ]);

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
                'message' => 'Gagal mengirim notifikasi via Firebase. Periksa log backend.',
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
