<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefStrategiIt extends Model
{
    protected $table = 'ref_strategi_it';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];
}
