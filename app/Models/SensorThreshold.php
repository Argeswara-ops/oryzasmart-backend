<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorThreshold extends Model
{
    use HasFactory;

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
