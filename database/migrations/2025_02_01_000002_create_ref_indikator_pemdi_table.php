<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_indikator_pemdi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ref_aspek_pemdi_id')->constrained('ref_aspek_pemdi')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_indikator_pemdi');
    }
};
