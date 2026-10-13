<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_stakeholder', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->constrained('mpr_perubahan')->cascadeOnDelete();
            $table->string('nama_stakeholder');
            $table->foreignId('ref_kategori_stakeholder_id')
                ->nullable()
                ->constrained('ref_kategori_stakeholder', indexName: 'mpr_stake_kategori_fk')
                ->nullOnDelete();
            $table->foreignId('ref_jenis_resistensi_id')
                ->nullable()
                ->constrained('ref_jenis_resistensi', indexName: 'mpr_stake_jenis_fk')
                ->nullOnDelete();
            $table->foreignId('ref_tingkat_resistensi_id')
                ->nullable()
                ->constrained('ref_tingkat_resistensi', indexName: 'mpr_stake_tingkat_fk')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_stakeholder');
    }
};
