<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MprImplementasi extends Model
{
    protected $table = 'mpr_implementasi';

    protected $fillable = [
        'mpr_perubahan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jangka_waktu',
        'pic_implementasi',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }
}
