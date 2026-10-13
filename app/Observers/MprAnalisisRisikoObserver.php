<?php

namespace App\Observers;

use App\Models\MprAnalisisRisiko;
use App\Models\MprPerubahan;
use App\Services\RiskMatrixCalculator;

/**
 * MprAnalisisRisikoObserver
 *
 * Pada saving:
 *  - layanan_id SELALU ditimpa dari perubahan->konteks->layanan_id (K2, anti tidak sinkron).
 *  - besaran_risiko dihitung bila level_kemungkinan / level_dampak berubah dan keduanya terisi.
 * Tidak ada re-rank prioritas pada MPR.
 */
class MprAnalisisRisikoObserver
{
    public function __construct(
        private readonly RiskMatrixCalculator $calculator
    ) {}

    public function saving(MprAnalisisRisiko $risiko): void
    {
        $layananId = MprPerubahan::withTrashed()
            ->with(['konteks' => fn ($q) => $q->withTrashed()])
            ->find($risiko->mpr_perubahan_id)
            ?->konteks
            ?->layanan_id;

        if ($layananId !== null) {
            $risiko->layanan_id = $layananId;
        }

        if (
            $risiko->isDirty(['level_kemungkinan', 'level_dampak']) &&
            $risiko->level_kemungkinan !== null &&
            $risiko->level_dampak !== null
        ) {
            $risiko->besaran_risiko = $this->calculator->hitung(
                (int) $risiko->level_kemungkinan,
                (int) $risiko->level_dampak
            );
        }
    }
}
