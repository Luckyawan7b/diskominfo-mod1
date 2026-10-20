<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MprPerubahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mpr_perubahan';

    protected $fillable = [
        'mpr_konteks_id',
        'mpr_analisis_prioritas_id',
        'id_perubahan',
        'nama_layanan_digital',
        'is_layanan_prioritas',
        'detail_perubahan',
        'unit_pemohon',
        'ref_klasifikasi_perubahan_id',
        'ref_lingkup_perubahan_id',
        'created_by',
    ];

    protected $casts = [
        'is_layanan_prioritas' => 'boolean',
    ];

    public function konteks(): BelongsTo
    {
        return $this->belongsTo(MprKonteks::class, 'mpr_konteks_id');
    }

    public function analisisPrioritas(): BelongsTo
    {
        return $this->belongsTo(MprAnalisisPrioritas::class, 'mpr_analisis_prioritas_id');
    }

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(RefKlasifikasiPerubahan::class, 'ref_klasifikasi_perubahan_id');
    }

    public function lingkup(): BelongsTo
    {
        return $this->belongsTo(RefLingkupPerubahan::class, 'ref_lingkup_perubahan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function analisisRisiko(): HasMany
    {
        return $this->hasMany(MprAnalisisRisiko::class, 'mpr_perubahan_id');
    }

    public function analisisOrganisasi(): HasOne
    {
        return $this->hasOne(MprAnalisisOrganisasi::class, 'mpr_perubahan_id');
    }

    public function persetujuan(): HasMany
    {
        return $this->hasMany(MprPersetujuan::class, 'mpr_perubahan_id');
    }

    public function implementasi(): HasOne
    {
        return $this->hasOne(MprImplementasi::class, 'mpr_perubahan_id');
    }

    public function sumberDaya(): HasMany
    {
        return $this->hasMany(MprSumberDaya::class, 'mpr_perubahan_id')->orderBy('urutan');
    }

    public function indikatorKeberhasilan(): HasMany
    {
        return $this->hasMany(MprIndikatorKeberhasilan::class, 'mpr_perubahan_id')->orderBy('urutan');
    }

    public function strategi(): HasOne
    {
        return $this->hasOne(MprStrategi::class, 'mpr_perubahan_id');
    }

    public function stakeholder(): HasMany
    {
        return $this->hasMany(MprStakeholder::class, 'mpr_perubahan_id');
    }

    public function komunikasi(): HasMany
    {
        return $this->hasMany(MprKomunikasi::class, 'mpr_perubahan_id');
    }

    public function pelatihan(): HasMany
    {
        return $this->hasMany(MprPelatihan::class, 'mpr_perubahan_id');
    }

    public function evaluasi(): HasOne
    {
        return $this->hasOne(MprEvaluasi::class, 'mpr_perubahan_id');
    }

    public function logbook(): HasOne
    {
        return $this->hasOne(MprLogbook::class, 'mpr_perubahan_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            if ($model->isForceDeleting()) {
                $model->logbook()->first()?->bersihkanLampiran();
                return;
            }

            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }
}
