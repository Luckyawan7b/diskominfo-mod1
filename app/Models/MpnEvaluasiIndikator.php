<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpnEvaluasiIndikator extends Model
{
    protected $table = 'mpn_evaluasi_indikator';

    protected $fillable = [
        'mpn_indikator_capaian_id',
        'realisasi',
        'analisis',
        'tindak_lanjut',
        'pelaksana_terkait'
    ];

    public function indikatorCapaian(): BelongsTo
    {
        return $this->belongsTo(MpnIndikatorCapaian::class, 'mpn_indikator_capaian_id');
    }

    protected function gap(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->realisasi !== null && $this->indikatorCapaian && $this->indikatorCapaian->kondisi_to_be !== null) {
                    return $this->realisasi - $this->indikatorCapaian->kondisi_to_be;
                }
                return null;
            }
        );
    }
}
