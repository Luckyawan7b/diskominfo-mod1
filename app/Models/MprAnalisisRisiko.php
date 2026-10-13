<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MprAnalisisRisiko extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mpr_analisis_risiko';

    protected $fillable = [
        'mpr_perubahan_id',
        'layanan_id',
        'kode_risiko',
        'sumber_risiko',
        'mr_risiko_id',
        'risiko',
        'penyebab',
        'level_kemungkinan',
        'level_dampak',
        'besaran_risiko',
        'perencanaan_keberlangsungan',
        'created_by',
    ];

    protected $casts = [
        'level_kemungkinan' => 'integer',
        'level_dampak' => 'integer',
        'besaran_risiko' => 'integer',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function mrRisiko(): BelongsTo
    {
        return $this->belongsTo(MrRisiko::class, 'mr_risiko_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy(): BelongsTo
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
}
