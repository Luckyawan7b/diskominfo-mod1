<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_komunikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->constrained('mpr_perubahan')->cascadeOnDelete();
            $table->text('informasi');
            $table->string('pengirim')->nullable();
            $table->string('target_audiens')->nullable();
            $table->string('media_komunikasi')->nullable();
            $table->string('periode_komunikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_komunikasi');
    }
};
