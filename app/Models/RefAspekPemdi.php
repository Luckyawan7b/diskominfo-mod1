<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefAspekPemdi extends Model
{
    protected $table = 'ref_aspek_pemdi';
    protected $fillable = ['nama'];

    public function indikatorPemdi(): HasMany
    {
        return $this->hasMany(RefIndikatorPemdi::class, 'ref_aspek_pemdi_id');
    }
}
