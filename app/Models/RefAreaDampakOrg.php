<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefAreaDampakOrg extends Model
{
    protected $table = 'ref_area_dampak_org';

    protected $fillable = [
        'nama',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function indikator(): HasMany
    {
        return $this->hasMany(RefIndikatorAreaDampak::class, 'ref_area_dampak_org_id')->orderBy('urutan');
    }
}
