<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefIndikatorAreaDampak extends Model
{
    protected $table = 'ref_indikator_area_dampak';

    protected $fillable = [
        'ref_area_dampak_org_id',
        'teks',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(RefAreaDampakOrg::class, 'ref_area_dampak_org_id');
    }
}
