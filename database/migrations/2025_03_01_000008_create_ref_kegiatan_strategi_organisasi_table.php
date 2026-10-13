<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_kegiatan_strategi_organisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ref_strategi_organisasi_id')
                ->constrained('ref_strategi_organisasi', indexName: 'ref_keg_strat_org_strategi_fk')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('no');
            $table->text('teks');
            $table->timestamps();

            $table->unique(['ref_strategi_organisasi_id', 'no'], 'ref_keg_strat_org_no_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_kegiatan_strategi_organisasi');
    }
};
