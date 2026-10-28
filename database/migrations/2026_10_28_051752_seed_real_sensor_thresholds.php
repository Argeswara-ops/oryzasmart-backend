<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('sensor_thresholds')->insertOrIgnore([
            ['sensor_type' => 'light_percent', 'min_value' => 20.0, 'max_value' => 90.0, 'created_at' => $now, 'updated_at' => $now],
            ['sensor_type' => 'uv_index', 'min_value' => 0.0, 'max_value' => 8.0, 'created_at' => $now, 'updated_at' => $now],
            ['sensor_type' => 'water_level', 'min_value' => 20.0, 'max_value' => 90.0, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('sensor_thresholds')
            ->where(function ($query): void {
                $query->where('sensor_type', 'light_percent')->where('min_value', 20.0)->where('max_value', 90.0);
            })
            ->orWhere(function ($query): void {
                $query->where('sensor_type', 'uv_index')->where('min_value', 0.0)->where('max_value', 8.0);
            })
            ->orWhere(function ($query): void {
                $query->where('sensor_type', 'water_level')->where('min_value', 20.0)->where('max_value', 90.0);
            })
            ->delete();
    }
};
