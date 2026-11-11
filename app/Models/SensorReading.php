<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'uptime_s',
        'temperature',
        'humidity',
        'nitrogen',
        'phosphorus',
        'potassium',
        'light',
        'light_percent',
        'uv_index',
        'ultrasonic',
        'distance_cm',
        'level_percent',
        'fan_state',
        'fan_mode',
        'recorded_at',
    ];

    protected $casts = [
        'uptime_s' => 'integer',
        'temperature' => 'float',
        'humidity' => 'float',
        'nitrogen' => 'float',
        'phosphorus' => 'float',
        'potassium' => 'float',
        'light' => 'float',
        'light_percent' => 'float',
        'uv_index' => 'float',
        'ultrasonic' => 'float',
        'distance_cm' => 'float',
        'level_percent' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function getWaterLevelPercentAttribute(): ?float
    {
        return $this->level_percent !== null ? (float) $this->level_percent : null;
    }
}
