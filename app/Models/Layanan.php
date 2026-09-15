<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'interoperabilitas' => 'boolean',
        'is_prioritas' => 'boolean',
        'tahun_pembuatan' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mrKonteks()
    {
        return $this->hasOne(MrKonteks::class, 'layanan_id');
    }

    public function mrKonteksHistory()
    {
        return $this->hasMany(MrKonteks::class, 'layanan_id');
    }
}
