<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MprOrgArea extends Model
{
    protected $table = 'mpr_org_area';

    protected $fillable = [
        'mpr_analisis_organisasi_id',
        'ref_area_dampak_org_id',
        'ref_ukuran_kompleksitas_id',
    ];

    public function analisisOrganisasi(): BelongsTo
    {
        return $this->belongsTo(MprAnalisisOrganisasi::class, 'mpr_analisis_organisasi_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(RefAreaDampakOrg::class, 'ref_area_dampak_org_id');
    }

    public function ukuranKompleksitas(): BelongsTo
    {
        return $this->belongsTo(RefUkuranKompleksitas::class, 'ref_ukuran_kompleksitas_id');
    }

    public function indikator(): BelongsToMany
    {
        return $this->belongsToMany(RefIndikatorAreaDampak::class, 'mpr_org_area_indikator', 'mpr_org_area_id', 'ref_indikator_area_dampak_id')->withTimestamps();
    }
}
