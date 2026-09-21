<?php

namespace App\Models;

use App\Contracts\HasLayananContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MrKonteks extends Model implements HasLayananContext
{
    use HasFactory, SoftDeletes;

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

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
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

    // ─── Lifecycle Hooks ──────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Form bersifat final tetapi selalu bisa diubah (CRUD biasa) */
    public function isEditableByOperator(): bool
    {
        return true;
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Scope: hanya konteks yang dapat diakses oleh $user.
     * - Admin: semua konteks
     * - Operator: hanya konteks dari layanan yang mereka buat (created_by)
     */
    public function scopeAccessibleBy($query, $user)
    {
        if ($user->isOperator()) {
            $query->whereHas('layanan', fn ($q) => $q->where('created_by', $user->id));
        }
        return $query;
    }

    public function isApproved(): bool
    {
        return true;
    }
}

