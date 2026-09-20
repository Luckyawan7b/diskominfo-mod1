<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefMetodePengolahan extends Model
{
    protected $table = 'ref_metode_pengolahan';
    protected $fillable = ['nama', 'deskripsi', 'contoh_output'];
}
