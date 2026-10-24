<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexVisionScanRequest;
use App\Http\Requests\StoreVisionScanRequest;
use App\Http\Resources\VisionScanResource;
use App\Models\VisionScan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

class VisionScanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexVisionScanRequest $request): AnonymousResourceCollection
    {
        $visionScans = $request->user()
            ->visionScans()
            ->when(
                $request->validated('scan_type'),
                fn ($query, string $scanType) => $query->where('scan_type', $scanType),
            )
            ->orderByDesc('scanned_at')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return VisionScanResource::collection($visionScans)->additional([
            'success' => true,
            'message' => 'Vision scans retrieved successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVisionScanRequest $request): JsonResponse
    {
        $user = $request->user();
        $clientId = $request->string('client_id')->toString();
        $existingVisionScan = $user->visionScans()->where('client_id', $clientId)->first();

        if ($existingVisionScan) {
            return (new VisionScanResource($existingVisionScan))
                ->additional([
                    'success' => true,
                    'message' => 'Vision scan already synchronized',
                ])
                ->response();
        }

        $imagePath = $request->file('image')->store("vision-scans/{$user->id}", 'public');

        try {
            $visionScan = $user->visionScans()->create([
                ...$request->safe()->except('image'),
                'client_id' => $clientId,
                'image_path' => $imagePath,
            ]);
            $status = 201;
            $message = 'Vision scan created successfully';
        } catch (UniqueConstraintViolationException $exception) {
            Storage::disk('public')->delete($imagePath);
            $visionScan = $user->visionScans()->where('client_id', $clientId)->first();

            if (! $visionScan) {
                throw $exception;
            }

            $status = 200;
            $message = 'Vision scan already synchronized';
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePath);

            throw $exception;
        }

        return (new VisionScanResource($visionScan))
            ->additional([
                'success' => true,
                'message' => $message,
            ])
            ->response()
            ->setStatusCode($status);
    }

    /**
     * Display the specified resource.
     */
    public function show(VisionScan $visionScan): VisionScanResource
    {
        Gate::authorize('view', $visionScan);

        return (new VisionScanResource($visionScan))->additional([
            'success' => true,
            'message' => 'Vision scan retrieved successfully',
        ]);
    }
}
