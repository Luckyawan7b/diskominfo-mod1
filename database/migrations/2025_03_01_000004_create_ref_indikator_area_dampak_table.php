<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_indikator_area_dampak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ref_area_dampak_org_id')
                ->constrained('ref_area_dampak_org', indexName: 'ref_ind_area_dampak_area_fk')
                ->cascadeOnDelete();
            $table->text('teks');
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_indikator_area_dampak');
    }
};
