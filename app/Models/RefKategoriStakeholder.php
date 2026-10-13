<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefKategoriStakeholder extends Model
{
    protected $table = 'ref_kategori_stakeholder';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];
}
