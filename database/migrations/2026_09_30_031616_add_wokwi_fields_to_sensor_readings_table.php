<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            $table->unsignedInteger('uptime_s')->nullable()->after('device_id');
            $table->float('light_percent')->nullable()->after('light');
            $table->float('uv_index')->nullable()->after('light_percent');
            $table->float('distance_cm')->nullable()->after('ultrasonic');
            $table->float('level_percent')->nullable()->after('distance_cm');
            $table->string('fan_state')->nullable()->after('level_percent');
            $table->string('fan_mode')->nullable()->after('fan_state');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            $table->dropColumn([
                'uptime_s',
                'light_percent',
                'uv_index',
                'distance_cm',
                'level_percent',
                'fan_state',
                'fan_mode',
            ]);
        });
    }
};
