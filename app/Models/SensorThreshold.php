<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorThreshold extends Model
{
    use HasFactory;

    public const SUPPORTED_TYPES = [
        'temperature',
        'humidity',
        'nitrogen',
        'phosphorus',
        'potassium',
        'light_percent',
        'uv_index',
        'water_level',
    ];

    public const DEFAULTS = [
        'temperature' => ['min_value' => 20.0, 'max_value' => 30.0],
        'humidity' => ['min_value' => 60.0, 'max_value' => 85.0],
        'nitrogen' => ['min_value' => 30.0, 'max_value' => 80.0],
        'phosphorus' => ['min_value' => 15.0, 'max_value' => 50.0],
        'potassium' => ['min_value' => 20.0, 'max_value' => 60.0],
        'light_percent' => ['min_value' => 20.0, 'max_value' => 90.0],
        'uv_index' => ['min_value' => 0.0, 'max_value' => 8.0],
        'water_level' => ['min_value' => 20.0, 'max_value' => 90.0],
    ];

    protected $fillable = [
        'sensor_type',
        'min_value',
        'max_value',
    ];

    protected $casts = [
        'min_value' => 'float',
        'max_value' => 'float',
    ];
}
