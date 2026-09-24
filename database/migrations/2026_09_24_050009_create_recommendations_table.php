<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->nullable()->constrained('alerts')->onDelete('cascade');
            $table->enum('status', ['INFO', 'WARNING', 'CRITICAL'])->default('INFO');
            $table->string('source');
            $table->text('condition');
            $table->text('recommendation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
