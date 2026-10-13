<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_prioritas_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_analisis_prioritas_id')
                ->constrained('mpr_analisis_prioritas', indexName: 'mpr_prio_ind_prioritas_fk')
                ->cascadeOnDelete();
            $table->foreignId('ref_indikator_pemdi_id')
                ->constrained('ref_indikator_pemdi', indexName: 'mpr_prio_ind_indikator_fk')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['mpr_analisis_prioritas_id', 'ref_indikator_pemdi_id'], 'mpr_prio_ind_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_prioritas_indikator');
    }
};
