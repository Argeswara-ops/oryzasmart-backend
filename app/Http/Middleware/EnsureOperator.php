<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperator
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'operator') {
            return response()->json([
                'success' => false,
                'message' => 'Akses operator diperlukan.',
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
