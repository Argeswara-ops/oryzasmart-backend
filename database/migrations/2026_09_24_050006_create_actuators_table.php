<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actuators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->default('fan');
            $table->enum('mode', ['AUTO', 'MANUAL'])->default('AUTO');
            $table->enum('status', ['ON', 'OFF', 'OFFLINE'])->default('OFF');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actuators');
    }
};
