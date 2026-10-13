<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MprPelatihan extends Model
{
    protected $table = 'mpr_pelatihan';

    protected $fillable = [
        'mpr_perubahan_id',
        'nama_pelatihan',
        'target_peserta',
        'target_jumlah',
        'metode_pelatihan',
        'tanggal_pelaksanaan',
    ];

    protected $casts = [
        'target_jumlah' => 'integer',
        'tanggal_pelaksanaan' => 'date',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }
}
