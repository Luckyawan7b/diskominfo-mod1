<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MprSumberDaya extends Model
{
    protected $table = 'mpr_sumber_daya';

    protected $fillable = [
        'mpr_perubahan_id',
        'jenis',
        'deskripsi',
        'alokasi_anggaran',
        'skema_pembiayaan',
        'pic',
        'urutan',
    ];

    protected $casts = [
        'alokasi_anggaran' => 'decimal:2',
        'urutan' => 'integer',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }
}
