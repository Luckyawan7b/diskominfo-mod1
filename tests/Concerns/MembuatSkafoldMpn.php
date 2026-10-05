<?php

namespace Tests\Concerns;

use App\Models\Role;
use App\Models\User;
use App\Models\Layanan;
use App\Models\MpnKonteks;
use App\Models\RefAspekPemdi;
use App\Models\RefIndikatorPemdi;
use App\Models\MpnPengetahuan;
use App\Models\RefMetodePengolahan;
use App\Models\MpnPengumpulan;

trait MembuatSkafoldMpn
{
    protected function siapkanDataMpn()
    {
        // 1. Roles & Users
        $roleAdmin = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $roleOperator = Role::firstOrCreate(['name' => 'operator'], ['label' => 'Operator']);

        $admin = User::factory()->create(['role_id' => $roleAdmin->id]);
        $operator = User::factory()->create(['role_id' => $roleOperator->id]);

        // 2. Layanan
        $layanan = Layanan::factory()->create(['created_by' => $operator->id]);
        
        // 3. Konteks MPN
        $konteks = MpnKonteks::create([
            'layanan_id' => $layanan->id,
            'tahun_penilaian' => 2026,
            'tahun_pelaksanaan' => 2026,
            'created_by' => $operator->id,
        ]);

        // 4. Referensi
        $aspek = RefAspekPemdi::firstOrCreate(['nama' => 'Aspek Tes']);
        $indikator = RefIndikatorPemdi::firstOrCreate([
            'ref_aspek_pemdi_id' => $aspek->id,
            'nama' => 'Indikator Tes'
        ]);
        $metode = RefMetodePengolahan::firstOrCreate(['nama' => 'Metode Tes']);

        // 5. Pengetahuan
        $pengetahuan = MpnPengetahuan::create([
            'mpn_konteks_id' => $konteks->id,
            'nama_sub_fitur' => 'Sub Fitur Tes',
            'layanan_prioritas' => true,
            'nama_pengetahuan' => 'Pengetahuan Tes',
            'sudah_terdokumentasi' => 'Belum',
            'ref_aspek_pemdi_id' => $aspek->id,
            'ref_indikator_pemdi_id' => $indikator->id,
            'apakah_terdokumentasi' => false,
            'created_by' => $operator->id,
        ]);

        // 6. Pengumpulan
        $pengumpulan = MpnPengumpulan::create([
            'mpn_pengetahuan_id' => $pengetahuan->id,
            'id_pengetahuan' => 'MRP-TEST-2026-001',
            'nama_pengetahuan' => 'Pengumpulan Tes',
            'tanggal_pengumpulan' => '2026-01-01',
            'unit_pengumpulan' => 'Unit Tes',
            'status_publikasi_simpan' => 'Draft',
            'tanggal_update_terakhir' => '2026-01-01',
        ]);

        return (object)[
            'admin' => $admin,
            'operator' => $operator,
            'layanan' => $layanan,
            'konteks' => $konteks,
            'aspek' => $aspek,
            'indikator' => $indikator,
            'metode' => $metode,
            'pengetahuan' => $pengetahuan,
            'pengumpulan' => $pengumpulan,
        ];
    }
}
