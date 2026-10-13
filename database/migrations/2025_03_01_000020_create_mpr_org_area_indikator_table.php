<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_org_area_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_org_area_id')
                ->constrained('mpr_org_area', indexName: 'mpr_org_ind_area_fk')
                ->cascadeOnDelete();
            $table->foreignId('ref_indikator_area_dampak_id')
                ->constrained('ref_indikator_area_dampak', indexName: 'mpr_org_ind_indikator_fk')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['mpr_org_area_id', 'ref_indikator_area_dampak_id'], 'mpr_org_ind_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_org_area_indikator');
    }
};
