<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefIndikatorPemdi extends Model
{
    protected $table = 'ref_indikator_pemdi';
    protected $fillable = ['ref_aspek_pemdi_id', 'nama'];

    public function aspekPemdi(): BelongsTo
    {
        return $this->belongsTo(RefAspekPemdi::class, 'ref_aspek_pemdi_id');
    }
}
