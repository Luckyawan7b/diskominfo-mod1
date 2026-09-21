<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

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

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
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
