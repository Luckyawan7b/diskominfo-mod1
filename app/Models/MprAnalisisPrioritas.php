<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MprAnalisisPrioritas extends Model
{
    protected $table = 'mpr_analisis_prioritas';

    protected $fillable = [
        'mpr_konteks_id',
        'nama_layanan_prioritas',
        'kondisi_perlu_berubah',
        'as_is',
        'to_be',
        'prioritas',
        'urutan',
    ];

    protected $casts = [
        'prioritas' => 'integer',
        'urutan' => 'integer',
    ];

    public function konteks(): BelongsTo
    {
        return $this->belongsTo(MprKonteks::class, 'mpr_konteks_id');
    }

    public function aspek(): BelongsToMany
    {
        return $this->belongsToMany(RefAspekPemdi::class, 'mpr_prioritas_aspek', 'mpr_analisis_prioritas_id', 'ref_aspek_pemdi_id')->withTimestamps();
    }

    public function indikator(): BelongsToMany
    {
        return $this->belongsToMany(RefIndikatorPemdi::class, 'mpr_prioritas_indikator', 'mpr_analisis_prioritas_id', 'ref_indikator_pemdi_id')->withTimestamps();
    }

    public function perubahan(): HasMany
    {
        return $this->hasMany(MprPerubahan::class, 'mpr_analisis_prioritas_id');
    }

    protected static function booted(): void
    {
        // K5: nama layanan prioritas selalu diisi otomatis (snapshot) dari layanan konteks, menimpa input user.
        static::creating(function (self $model) {
            $nama = $model->konteks?->layanan?->nama_layanan;
            if ($nama !== null) {
                $model->nama_layanan_prioritas = $nama;
            }
        });
    }
}
