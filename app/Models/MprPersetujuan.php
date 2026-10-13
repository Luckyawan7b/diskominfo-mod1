<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MprPersetujuan extends Model
{
    protected $table = 'mpr_persetujuan';

    protected $fillable = [
        'mpr_perubahan_id',
        'tahap',
        'status',
        'pic_penyetuju',
        'catatan',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }
}
