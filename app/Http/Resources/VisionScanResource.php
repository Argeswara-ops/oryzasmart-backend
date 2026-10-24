<?php

namespace App\Http\Resources;

use App\Models\VisionScan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class VisionScanResource extends JsonResource
{
    /** @var VisionScan */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'local_id' => $this->resource->client_id,
            'client_id' => $this->resource->client_id,
            'scan_type' => $this->resource->scan_type,
            'class_name' => $this->resource->class_name,
            'display_name' => $this->resource->display_name,
            'confidence' => $this->resource->confidence,
            'scanned_at' => $this->resource->scanned_at->toISOString(),
            'image_url' => URL::to(Storage::disk('public')->url($this->resource->image_path)),
            'created_at' => $this->resource->created_at->toISOString(),
        ];
    }
}
