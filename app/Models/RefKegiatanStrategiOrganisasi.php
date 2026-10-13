<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefKegiatanStrategiOrganisasi extends Model
{
    protected $table = 'ref_kegiatan_strategi_organisasi';

    protected $fillable = [
        'ref_strategi_organisasi_id',
        'no',
        'teks',
    ];

    protected $casts = [
        'no' => 'integer',
    ];

    public function strategi(): BelongsTo
    {
        return $this->belongsTo(RefStrategiOrganisasi::class, 'ref_strategi_organisasi_id');
    }
}
