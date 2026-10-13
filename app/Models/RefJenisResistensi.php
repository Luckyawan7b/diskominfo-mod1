<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefJenisResistensi extends Model
{
    protected $table = 'ref_jenis_resistensi';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];
}
