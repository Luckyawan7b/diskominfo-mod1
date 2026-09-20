<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpnRencanaDokumentasi extends Model
{
    protected $table = 'mpn_rencana_dokumentasi';

    protected $fillable = [
        'mpn_pengetahuan_id',
        'target_tahun_ini',
        'pemilik_pengetahuan',
        'tipe_teks',
        'tipe_gambar',
        'tipe_audio',
        'tipe_video',
        'penanggung_jawab',
        'target_waktu_dokumentasi'
    ];

    public function pengetahuan(): BelongsTo
    {
        return $this->belongsTo(MpnPengetahuan::class, 'mpn_pengetahuan_id');
    }
}
