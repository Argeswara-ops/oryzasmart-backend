<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detection extends Model
{
    use HasFactory;

    protected $fillable = [
        'camera_id',
        'detection_type',
        'class_name',
        'confidence',
        'image_path',
        'detected_at',
    ];

    protected $casts = [
        'confidence' => 'float',
        'detected_at' => 'datetime',
    ];

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }
}
