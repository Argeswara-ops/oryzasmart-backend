<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class PaddyPredictionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ]);

        $response = $this->sendImageToVision(
            image: $validated['image'],
            endpoint: '/predict',
            timeout: 60,
        );

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Vision prediction failed',
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'prediction' => $response->json('prediction'),
        ]);
    }

    public function realtime(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'stream_id' => ['nullable', 'string', 'max:100'],
        ]);

        $response = $this->sendImageToVision(
            image: $validated['image'],
            endpoint: '/predict/realtime',
            timeout: 30,
            data: [
                'stream_id' => $validated['stream_id'] ?? null,
            ],
        );

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Realtime vision prediction failed',
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function sendImageToVision(UploadedFile $image, string $endpoint, int $timeout, array $data = []): Response
    {
        return Http::connectTimeout(5)
            ->timeout($timeout)
            ->withHeaders([
                'X-API-Key' => config('services.oryza_vision.api_key'),
            ])
            ->attach(
                'file',
                file_get_contents($image->getRealPath()),
                $image->getClientOriginalName(),
            )
            ->post($this->visionUrl($endpoint), $data);
    }

    private function visionUrl(string $endpoint): string
    {
        return rtrim((string) config('services.oryza_vision.url'), '/').$endpoint;
    }
}
