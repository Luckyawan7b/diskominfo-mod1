<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefStrategiOrganisasi extends Model
{
    protected $table = 'ref_strategi_organisasi';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];

    public function kegiatan(): HasMany
    {
        return $this->hasMany(RefKegiatanStrategiOrganisasi::class, 'ref_strategi_organisasi_id')->orderBy('no');
    }
}
