<?php

namespace App\Models;

use App\Contracts\HasLayananContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MprKonteks extends Model implements HasLayananContext
{
    use HasFactory, SoftDeletes;

    protected $table = 'mpr_konteks';

    protected $fillable = [
        'layanan_id',
        'nama_instansi',
        'nama_opd_terkait',
        'tahun_penilaian',
        'periode_perencanaan',
        'created_by',
    ];

    protected $casts = [
        'tahun_penilaian' => 'integer',
    ];

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function analisisPrioritas(): HasMany
    {
        return $this->hasMany(MprAnalisisPrioritas::class, 'mpr_konteks_id')->orderBy('urutan');
    }

    public function perubahan(): HasMany
    {
        return $this->hasMany(MprPerubahan::class, 'mpr_konteks_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            if ($model->isForceDeleting()) {
                $perubahanIds = MprPerubahan::withTrashed()
                    ->where('mpr_konteks_id', $model->id)
                    ->pluck('id');

                if ($perubahanIds->isNotEmpty()) {
                    MprLogbook::whereIn('mpr_perubahan_id', $perubahanIds)
                        ->get()
                        ->each(fn (MprLogbook $logbook) => $logbook->bersihkanLampiran());
                }

                return;
            }

            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function isEditableByOperator(): bool
    {
        return true;
    }
}
