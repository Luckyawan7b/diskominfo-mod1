<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_strategi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_perubahan_id')->unique()->constrained('mpr_perubahan')->cascadeOnDelete();
            $table->foreignId('ref_strategi_it_id')
                ->nullable()
                ->constrained('ref_strategi_it', indexName: 'mpr_strategi_it_fk')
                ->nullOnDelete();
            $table->text('deskripsi_strategi_it')->nullable();
            $table->text('rollback_plan')->nullable();
            $table->foreignId('ref_strategi_organisasi_id')
                ->nullable()
                ->constrained('ref_strategi_organisasi', indexName: 'mpr_strategi_org_fk')
                ->nullOnDelete();
            $table->text('detail_strategi_organisasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_strategi');
    }
};
