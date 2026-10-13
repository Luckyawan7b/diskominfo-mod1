<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefKlasifikasiPerubahan extends Model
{
    protected $table = 'ref_klasifikasi_perubahan';

    protected $fillable = [
        'nama',
        'deskripsi',
        'wajib_analisis_dampak',
        'wajib_persetujuan',
    ];

    protected $casts = [
        'wajib_analisis_dampak' => 'boolean',
        'wajib_persetujuan' => 'boolean',
    ];

    public function perubahan(): HasMany
    {
        return $this->hasMany(MprPerubahan::class, 'ref_klasifikasi_perubahan_id');
    }
}
