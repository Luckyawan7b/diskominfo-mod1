<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MprStrategi extends Model
{
    protected $table = 'mpr_strategi';

    protected $fillable = [
        'mpr_perubahan_id',
        'ref_strategi_it_id',
        'deskripsi_strategi_it',
        'rollback_plan',
        'ref_strategi_organisasi_id',
        'detail_strategi_organisasi',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }

    public function strategiIt(): BelongsTo
    {
        return $this->belongsTo(RefStrategiIt::class, 'ref_strategi_it_id');
    }

    public function strategiOrganisasi(): BelongsTo
    {
        return $this->belongsTo(RefStrategiOrganisasi::class, 'ref_strategi_organisasi_id');
    }
}
