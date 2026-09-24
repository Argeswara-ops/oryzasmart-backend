<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camera_id')->constrained('cameras')->onDelete('cascade');
            $table->enum('detection_type', ['PEST', 'DISEASE', 'HEALTHY', 'UNKNOWN'])->default('UNKNOWN');
            $table->string('class_name');
            $table->float('confidence');
            $table->string('image_path')->nullable();
            $table->timestamp('detected_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detections');
    }
};
