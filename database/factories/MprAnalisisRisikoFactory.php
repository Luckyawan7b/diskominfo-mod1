<?php

namespace Database\Factories;

use App\Models\MprAnalisisRisiko;
use App\Models\MprPerubahan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MprAnalisisRisiko>
 */
class MprAnalisisRisikoFactory extends Factory
{
    protected $model = MprAnalisisRisiko::class;

    public function definition(): array
    {
        return [
            'mpr_perubahan_id' => MprPerubahan::factory(),
            // Denormalisasi; observer (fase 5) juga akan menimpa dari relasi.
            'layanan_id'       => fn (array $attrs) => MprPerubahan::with('konteks')->find($attrs['mpr_perubahan_id'])->konteks->layanan_id,
            'kode_risiko'      => 'R-PR-' . fake()->unique()->numerify('####'),
            'sumber_risiko'    => 'baru',
            'risiko'           => fake()->sentence(),
            'penyebab'         => fake()->sentence(),
            'created_by'       => User::factory()->operator(),
        ];
    }

    /**
     * State: level kemungkinan & dampak terisi (besaran dihitung observer pada fase 5).
     */
    public function withLevels(int $kemungkinan = 3, int $dampak = 3): static
    {
        return $this->state(fn () => [
            'level_kemungkinan' => $kemungkinan,
            'level_dampak'      => $dampak,
        ]);
    }
}
