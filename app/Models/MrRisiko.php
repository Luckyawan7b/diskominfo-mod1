<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MrRisiko extends Model
{
    use SoftDeletes;

    protected $table = 'mr_risiko';

    protected $fillable = [
        'mr_konteks_id',
        'mr_sasaran_upr_id',
        'sasaran_pembangunan_nasional_snapshot',
        'sasaran_upr_snapshot',
        'indikator_kinerja_snapshot',
        'kode_risiko',
        'peristiwa_risiko',
        'kategori_risiko',
        'penyebab',
        'dampak',
        'area_dampak',
        'level_kemungkinan',
        'level_dampak',
        'besaran_risiko',
        'prioritas_risiko',
        'created_by',
    ];

    protected $casts = [
        'level_kemungkinan' => 'integer',
        'level_dampak'      => 'integer',
        'besaran_risiko'    => 'integer',
        'prioritas_risiko'  => 'integer',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function konteks(): BelongsTo
    {
        return $this->belongsTo(MrKonteks::class, 'mr_konteks_id');
    }

    public function sasaran(): BelongsTo
    {
        return $this->belongsTo(MrSasaranUpr::class, 'mr_sasaran_upr_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function perlakuan(): HasOne
    {
        return $this->hasOne(MrRisikoPerlakuan::class);
    }

    public function residual(): HasOne
    {
        return $this->hasOne(MrRisikoResidual::class);
    }

    public function kolomTambahan(): HasOne
    {
        return $this->hasOne(MrKolomTambahan::class);
    }

    public function layananDigital(): HasOne
    {
        return $this->hasOne(MrLayananDigital::class);
    }

    public function pemantauan(): HasMany
    {
        return $this->hasMany(MrPemantauanRisiko::class);
    }

    /** Lampiran yang langsung attached ke risiko (bukan ke pemantauan) */
    public function lampiran(): MorphMany
    {
        return $this->morphMany(MrLampiran::class, 'lampirable');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isEditableByOperator(): bool
    {
        return true;
    }

    public function isRejected(): bool
    {
        return false;
    }
}
