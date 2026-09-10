<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mr_struktur_pelaksana', function (Blueprint $table) {
            $table->id();
            // 1:1 dengan mr_konteks
            $table->foreignId('mr_konteks_id')->unique()->constrained('mr_konteks')->cascadeOnDelete();
            $table->string('pemilik_risiko')->nullable();
            $table->string('koordinator_risiko')->nullable();
            $table->text('pengelola_risiko')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_struktur_pelaksana');
    }
};
