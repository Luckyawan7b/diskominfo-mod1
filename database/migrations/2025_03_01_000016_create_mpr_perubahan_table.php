<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpr_perubahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpr_konteks_id')->constrained('mpr_konteks')->cascadeOnDelete();
            $table->foreignId('mpr_analisis_prioritas_id')
                ->nullable()
                ->constrained('mpr_analisis_prioritas', indexName: 'mpr_perubahan_prioritas_fk')
                ->nullOnDelete();
            $table->string('id_perubahan', 100)->unique();
            $table->string('nama_layanan_digital');
            $table->boolean('is_layanan_prioritas')->default(false);
            $table->text('detail_perubahan');
            $table->string('unit_pemohon')->nullable();
            $table->foreignId('ref_klasifikasi_perubahan_id')
                ->constrained('ref_klasifikasi_perubahan', indexName: 'mpr_perubahan_klasifikasi_fk');
            $table->foreignId('ref_lingkup_perubahan_id')
                ->constrained('ref_lingkup_perubahan', indexName: 'mpr_perubahan_lingkup_fk');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpr_perubahan');
    }
};
