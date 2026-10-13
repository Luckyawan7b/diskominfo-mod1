<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefUkuranKompleksitas extends Model
{
    protected $table = 'ref_ukuran_kompleksitas';

    protected $fillable = [
        'nama',
        'definisi',
        'bobot',
    ];

    protected $casts = [
        'bobot' => 'integer',
    ];
}
