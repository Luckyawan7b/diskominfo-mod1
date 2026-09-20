<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MpnIndikatorCapaian extends Model
{
    protected $table = 'mpn_indikator_capaian';

    protected $fillable = [
        'mpn_konteks_id',
        'urutan',
        'indikator',
        'kondisi_as_is',
        'kondisi_to_be'
    ];

    public function konteks(): BelongsTo
    {
        return $this->belongsTo(MpnKonteks::class, 'mpn_konteks_id');
    }

    public function evaluasi(): HasOne
    {
        return $this->hasOne(MpnEvaluasiIndikator::class, 'mpn_indikator_capaian_id');
    }
}
