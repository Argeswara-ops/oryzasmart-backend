<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vision_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_id', 100);
            $table->enum('scan_type', ['disease', 'pest']);
            $table->string('class_name', 100);
            $table->string('display_name', 150);
            $table->decimal('confidence', 8, 7);
            $table->string('image_path');
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->unique(['user_id', 'client_id']);
            $table->index(['user_id', 'scan_type', 'scanned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vision_scans');
    }
};
