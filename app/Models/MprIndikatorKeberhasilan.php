<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MprIndikatorKeberhasilan extends Model
{
    protected $table = 'mpr_indikator_keberhasilan';

    protected $fillable = [
        'mpr_perubahan_id',
        'indikator',
        'satuan',
        'target',
        'realisasi',
        'urutan',
    ];

    protected $casts = [
        'target' => 'decimal:4',
        'realisasi' => 'decimal:4',
        'urutan' => 'integer',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }
}
