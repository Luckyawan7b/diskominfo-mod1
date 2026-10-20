<?php

namespace Tests\Concerns;

use App\Models\Layanan;
use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\Role;
use App\Models\User;

trait MembuatSkafoldMpr
{
    protected function siapkanDataMpr(): object
    {
        // 1. Roles & Users
        $roleAdmin = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $roleOperator = Role::firstOrCreate(['name' => 'operator'], ['label' => 'Operator']);

        $admin = User::factory()->create(['role_id' => $roleAdmin->id]);
        $operator = User::factory()->create(['role_id' => $roleOperator->id]);

        // 2. Layanan
        $layanan = Layanan::factory()->create(['created_by' => $operator->id]);

        // 3. Konteks MPR
        $konteks = MprKonteks::create([
            'layanan_id'      => $layanan->id,
            'nama_instansi'   => $operator->nama_dinas ?? 'Dinas Tes',
            'tahun_penilaian' => 2026,
            'created_by'      => $operator->id,
        ]);

        // 4. Perubahan
        $perubahan = MprPerubahan::factory()->create([
            'mpr_konteks_id' => $konteks->id,
            'created_by'     => $operator->id,
        ]);

        return (object) [
            'admin'     => $admin,
            'operator'  => $operator,
            'layanan'   => $layanan,
            'konteks'   => $konteks,
            'perubahan' => $perubahan,
        ];
    }

    /**
     * Operator lain beserta layanannya (untuk uji penolakan 403).
     */
    protected function buatOperatorLainMpr(): object
    {
        $roleOperator = Role::firstOrCreate(['name' => 'operator'], ['label' => 'Operator']);
        $operator = User::factory()->create(['role_id' => $roleOperator->id]);
        $layanan = Layanan::factory()->create(['created_by' => $operator->id]);

        return (object) compact('operator', 'layanan');
    }
}
