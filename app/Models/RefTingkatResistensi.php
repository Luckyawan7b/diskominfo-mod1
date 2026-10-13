<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefTingkatResistensi extends Model
{
    protected $table = 'ref_tingkat_resistensi';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];
}
