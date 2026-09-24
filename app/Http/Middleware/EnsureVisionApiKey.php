<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVisionApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.vision.api_key');

        if ($configuredKey === '' && app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        if ($configuredKey === '' || ! hash_equals($configuredKey, (string) $request->header('X-Vision-Key'))) {
            return response()->json([
                'success' => false,
                'message' => 'Vision API key tidak valid.',
                'errors' => null,
            ], 401);
        }

        return $next($request);
    }
}
