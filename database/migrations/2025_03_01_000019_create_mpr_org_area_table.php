<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_org_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_analisis_organisasi_id')
                ->constrained('mpr_analisis_organisasi', indexName: 'mpr_org_area_analisis_fk')
                ->cascadeOnDelete();
            $table->foreignId('ref_area_dampak_org_id')
                ->constrained('ref_area_dampak_org', indexName: 'mpr_org_area_area_fk');
            $table->foreignId('ref_ukuran_kompleksitas_id')
                ->nullable()
                ->constrained('ref_ukuran_kompleksitas', indexName: 'mpr_org_area_ukuran_fk')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['mpr_analisis_organisasi_id', 'ref_area_dampak_org_id'], 'mpr_org_area_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_org_area');
    }
};
