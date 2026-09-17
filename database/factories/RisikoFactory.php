<?php

namespace Database\Factories;

use App\Models\MrKonteks;
use App\Models\MrRisiko;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MrRisiko>
 */
class RisikoFactory extends Factory
{
    protected $model = MrRisiko::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mr_konteks_id'    => MrKonteks::factory(),
            'kode_risiko'      => strtoupper(fake()->lexify('??')) . '-R-' . fake()->numberBetween(1, 99),
            'peristiwa_risiko' => fake()->sentence(),
            'kategori_risiko'  => fake()->randomElement([
                'Risiko Operasional',
                'Risiko Keamanan Informasi',
                'Risiko Strategis',
                'Risiko Kepatuhan',
            ]),
            'created_by'       => User::factory()->operator(),
        ];
    }

    /**
     * State: risiko dengan level kemungkinan dan dampak terisi (besaran akan dihitung observer).
     */
    public function withLevels(int $kemungkinan = 3, int $dampak = 3): static
    {
        return $this->state(fn () => [
            'level_kemungkinan' => $kemungkinan,
            'level_dampak'      => $dampak,
        ]);
    }

    /**
     * State: risiko dalam konteks tertentu.
     */
    public function inKonteks(MrKonteks $konteks): static
    {
        return $this->state(fn () => ['mr_konteks_id' => $konteks->id]);
    }
}
