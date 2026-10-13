<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MprAnalisisOrganisasi extends Model
{
    protected $table = 'mpr_analisis_organisasi';

    protected $fillable = [
        'mpr_perubahan_id',
        'ref_ukuran_kompleksitas_id',
        'rekomendasi',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }

    public function ukuranKompleksitas(): BelongsTo
    {
        return $this->belongsTo(RefUkuranKompleksitas::class, 'ref_ukuran_kompleksitas_id');
    }

    public function areas(): HasMany
    {
        return $this->hasMany(MprOrgArea::class, 'mpr_analisis_organisasi_id');
    }
}
