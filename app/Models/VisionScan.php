<?php

namespace App\Models;

use Database\Factories\VisionScanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisionScan extends Model
{
    /** @use HasFactory<VisionScanFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'scan_type',
        'class_name',
        'display_name',
        'confidence',
        'image_path',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'scanned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
