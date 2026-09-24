<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpn_pengumpulan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpn_pengetahuan_id')->constrained('mpn_pengetahuan')->cascadeOnDelete();
            $table->foreignId('revisi_dari_id')->nullable()->constrained('mpn_pengumpulan')->nullOnDelete();
            $table->string('id_pengetahuan')->unique();
            $table->date('tanggal_pengumpulan')->nullable();
            $table->string('unit_pengumpulan')->nullable();
            $table->string('lokasi_penyimpanan_lain')->nullable();
            $table->text('keterangan_lokasi_lainnya')->nullable();
            $table->enum('status_publikasi_simpan', ['Draft', 'Ditolak', 'Dipublikasikan', 'Diarsipkan'])->nullable();
            $table->foreignId('ref_metode_pengolahan_id')->nullable()->constrained('ref_metode_pengolahan')->nullOnDelete();
            $table->text('deskripsi_pengolahan')->nullable();
            $table->date('tanggal_update_terakhir')->nullable();
            $table->decimal('rating_pengetahuan', 3, 2)->nullable();

            // Kolom Metadata
            $table->string('penulis')->nullable();
            $table->string('label_tags')->nullable();
            $table->string('kontributor')->nullable();
            $table->string('url')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpn_pengumpulan');
    }
};
