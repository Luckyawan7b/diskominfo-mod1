<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MrKonteks extends Model
{
    use SoftDeletes;

    protected $table = 'mr_konteks';

    protected $fillable = [
        'layanan_id',
        'nama_instansi',
        'nama_upr',
        'tugas_upr',
        'fungsi_upr',
        'tahun_penilaian',
        'tahun_pelaksanaan',
        'selera_risiko',
        'created_by',
    ];

    protected $casts = [
        'tahun_penilaian'   => 'integer',
        'tahun_pelaksanaan' => 'integer',
        'selera_risiko'     => 'integer',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sasaranUpr(): HasMany
    {
        return $this->hasMany(MrSasaranUpr::class, 'mr_konteks_id')->orderBy('urutan');
    }

    public function strukturPelaksana(): HasOne
    {
        return $this->hasOne(MrStrukturPelaksana::class);
    }

    public function risiko(): HasMany
    {
        return $this->hasMany(MrRisiko::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Form bersifat final tetapi selalu bisa diubah (CRUD biasa) */
    public function isEditableByOperator(): bool
    {
        return true;
    }

    public function isApproved(): bool
    {
        return true;
    }
}
