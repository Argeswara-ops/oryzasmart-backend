<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('cascade');
            $table->foreignId('camera_id')->nullable()->constrained('cameras')->onDelete('cascade');
            $table->foreignId('detection_id')->nullable()->constrained('detections')->onDelete('cascade');
            $table->string('type');
            $table->string('source');
            $table->enum('severity', ['INFO', 'WARNING', 'CRITICAL'])->default('INFO');
            $table->string('title');
            $table->text('message');
            $table->enum('status', ['UNREAD', 'READ'])->default('UNREAD');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
