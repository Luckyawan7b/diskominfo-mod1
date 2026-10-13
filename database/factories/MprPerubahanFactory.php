<?php

namespace Database\Factories;

use App\Models\MprKonteks;
use App\Models\MprPerubahan;
use App\Models\RefKlasifikasiPerubahan;
use App\Models\RefLingkupPerubahan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MprPerubahan>
 */
class MprPerubahanFactory extends Factory
{
    protected $model = MprPerubahan::class;

    public function definition(): array
    {
        return [
            'mpr_konteks_id'               => MprKonteks::factory(),
            // Format produksi dibuat MprIdGenerator (fase 5); factory memakai nilai unik sederhana.
            'id_perubahan'                 => 'MPR-TEST-' . date('Y') . '-' . fake()->unique()->numerify('######'),
            'nama_layanan_digital'         => 'Layanan ' . fake()->word(),
            'is_layanan_prioritas'         => false,
            'detail_perubahan'             => fake()->sentence(),
            'unit_pemohon'                 => 'Unit ' . fake()->word(),
            'ref_klasifikasi_perubahan_id' => fn () => $this->klasifikasi('Normal', true, true),
            'ref_lingkup_perubahan_id'     => fn () => $this->lingkup('Teknis', true, false),
            'created_by'                   => User::factory()->operator(),
        ];
    }

    public function klasifikasiStandard(): static
    {
        return $this->state(fn () => ['ref_klasifikasi_perubahan_id' => $this->klasifikasi('Standard', false, false)]);
    }

    public function klasifikasiEmergency(): static
    {
        return $this->state(fn () => ['ref_klasifikasi_perubahan_id' => $this->klasifikasi('Emergency', true, true)]);
    }

    public function lingkupOrganisasi(): static
    {
        return $this->state(fn () => ['ref_lingkup_perubahan_id' => $this->lingkup('Organisasi', false, true)]);
    }

    public function lingkupTeknisOrganisasi(): static
    {
        return $this->state(fn () => ['ref_lingkup_perubahan_id' => $this->lingkup('Teknis & Organisasi', true, true)]);
    }

    private function klasifikasi(string $nama, bool $dampak, bool $persetujuan): int
    {
        return RefKlasifikasiPerubahan::firstOrCreate(
            ['nama' => $nama],
            ['wajib_analisis_dampak' => $dampak, 'wajib_persetujuan' => $persetujuan],
        )->id;
    }

    private function lingkup(string $nama, bool $teknis, bool $organisasi): int
    {
        return RefLingkupPerubahan::firstOrCreate(
            ['nama' => $nama],
            ['butuh_analisis_teknis' => $teknis, 'butuh_analisis_organisasi' => $organisasi],
        )->id;
    }
}
