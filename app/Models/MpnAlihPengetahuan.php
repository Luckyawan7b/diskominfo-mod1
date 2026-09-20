<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpnAlihPengetahuan extends Model
{
    protected $table = 'mpn_alih_pengetahuan';

    protected $fillable = [
        'mpn_pengumpulan_id',
        'tanggal_kegiatan',
        'metode_pelatihan',
        'metode_workshop',
        'metode_sosialisasi',
        'metode_mentoring',
        'metode_sharing',
        'metode_lainnya',
        'keterangan_lainnya',
        'penerima_pengetahuan',
        'hasil_evaluasi'
    ];

    public function pengumpulan(): BelongsTo
    {
        return $this->belongsTo(MpnPengumpulan::class, 'mpn_pengumpulan_id');
    }
}
