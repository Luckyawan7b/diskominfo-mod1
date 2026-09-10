<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('layanans', function (Blueprint $table) {
            $table->id();
            $table->string('unit_pelaksana')->nullable();
            $table->string('bidang_bagian')->nullable();
            $table->enum('status_layanan', ['berjalan', 'direncanakan', 'dihentikan'])->default('berjalan');
            $table->string('nama_layanan');
            $table->text('deskripsi_layanan')->nullable();
            $table->enum('target_pengguna', ['publik/masyarakat', 'internal pemerintahan'])->nullable();
            $table->string('kl_terkait')->nullable();
            $table->string('supplier_data')->nullable();
            $table->text('nama_data_input')->nullable();
            $table->text('nama_data_output')->nullable();
            $table->enum('sifat_data', ['terbuka', 'terbatas', 'tertutup'])->nullable();
            $table->string('jenis_data')->nullable();
            $table->string('validitas_data')->nullable();
            $table->boolean('interoperabilitas')->default(false);
            $table->text('tujuan_integrasi')->nullable();
            $table->string('metode_integrasi')->nullable();
            $table->string('link_dokumen_integrasi')->nullable();
            $table->string('nama_aplikasi')->nullable();
            $table->string('tipe_aplikasi')->nullable();
            $table->string('link_aplikasi')->nullable();
            $table->text('keluaran_aplikasi')->nullable();
            $table->string('letak_server')->nullable();
            $table->string('link_dpa')->nullable();
            $table->integer('tahun_pembuatan')->nullable();
            $table->string('link_sla')->nullable();
            $table->string('link_sop')->nullable();
            $table->string('helpdesk')->nullable();
            $table->boolean('is_prioritas')->default(false);

            // Relasi ke pembuat layanan (user OPD/Dinas)
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanans');
    }
};
