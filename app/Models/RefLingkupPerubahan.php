<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefLingkupPerubahan extends Model
{
    protected $table = 'ref_lingkup_perubahan';

    protected $fillable = [
        'nama',
        'deskripsi',
        'butuh_analisis_teknis',
        'butuh_analisis_organisasi',
    ];

    protected $casts = [
        'butuh_analisis_teknis' => 'boolean',
        'butuh_analisis_organisasi' => 'boolean',
    ];

    public function perubahan(): HasMany
    {
        return $this->hasMany(MprPerubahan::class, 'ref_lingkup_perubahan_id');
    }
}
