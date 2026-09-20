<?php

namespace App\Observers;

use App\Models\MpnPengetahuan;
use App\Models\MpnRencanaDokumentasi;

class MpnPengetahuanObserver
{
    /**
     * Handle the MpnPengetahuan "saved" event.
     * Dipanggil setiap kali model dibuat atau diupdate.
     */
    public function saved(MpnPengetahuan $pengetahuan): void
    {
        // 0 atau false atau '0' semuanya dianggap "Tidak" (belum terdokumentasi)
        // Di UI biasanya akan dikirim sebagai boolean atau string '0'/'1'
        $isTidakTerdokumentasi = $pengetahuan->apakah_terdokumentasi === false || 
                                 $pengetahuan->apakah_terdokumentasi === 0 || 
                                 $pengetahuan->apakah_terdokumentasi === '0';

        if ($isTidakTerdokumentasi) {
            MpnRencanaDokumentasi::firstOrCreate([
                'mpn_pengetahuan_id' => $pengetahuan->id,
            ]);
        } else {
            MpnRencanaDokumentasi::where('mpn_pengetahuan_id', $pengetahuan->id)->delete();
        }
    }
}
