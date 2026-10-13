<?php

namespace Database\Factories;

use App\Models\Layanan;
use App\Models\MprKonteks;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MprKonteks>
 */
class MprKonteksFactory extends Factory
{
    protected $model = MprKonteks::class;

    public function definition(): array
    {
        return [
            'layanan_id'          => Layanan::factory(),
            'nama_instansi'       => 'Dinas ' . fake()->word() . ' Kabupaten Test',
            'nama_opd_terkait'    => null,
            'tahun_penilaian'     => (int) date('Y'),
            'periode_perencanaan' => 'Jan–Des ' . date('Y'),
            'created_by'          => User::factory()->operator(),
        ];
    }

    /**
     * State: konteks yang dimiliki oleh user tertentu (layanan + created_by satu owner).
     */
    public function ownedBy(User $user, Layanan $layanan): static
    {
        return $this->state(fn () => [
            'layanan_id'    => $layanan->id,
            'created_by'    => $user->id,
            'nama_instansi' => $user->nama_dinas ?? 'Dinas Kominfo Test',
        ]);
    }
}
