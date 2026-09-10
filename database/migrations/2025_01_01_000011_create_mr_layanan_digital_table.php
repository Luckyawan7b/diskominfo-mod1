<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mr_layanan_digital', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mr_risiko_id')->constrained('mr_risiko')->cascadeOnDelete();
            $table->boolean('perlu_mkb')->nullable();
            $table->string('pic')->nullable();
            $table->string('target_waktu_penyusunan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_layanan_digital');
    }
};
