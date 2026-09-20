<?php

namespace App\Observers;

use App\Models\MpnPemanfaatan;

class MpnPemanfaatanObserver
{
    /**
     * Hitung ulang average rating untuk MpnPengumpulan yang terkait
     */
    private function updateRatingPengetahuan(MpnPemanfaatan $pemanfaatan): void
    {
        $pengumpulan = $pemanfaatan->pengumpulan;
        if ($pengumpulan) {
            $avgRating = $pengumpulan->pemanfaatan()->avg('rating');
            
            // updateQuietly agar tidak men-trigger event update/saved jika ada observer lain
            $pengumpulan->updateQuietly([
                'rating_pengetahuan' => $avgRating
            ]);
        }
    }

    public function saved(MpnPemanfaatan $pemanfaatan): void
    {
        $this->updateRatingPengetahuan($pemanfaatan);
    }

    public function deleted(MpnPemanfaatan $pemanfaatan): void
    {
        $this->updateRatingPengetahuan($pemanfaatan);
    }
}
