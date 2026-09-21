<?php

namespace App\Models;

use App\Contracts\HasLayananContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MpnKonteks extends Model implements HasLayananContext
{
    use SoftDeletes;

    protected $table = 'mpn_konteks';

    protected $fillable = [
        'layanan_id',
        'tahun_penilaian',
        'tahun_pelaksanaan',
        'created_by'
    ];

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

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function indikatorCapaian(): HasMany
    {
        return $this->hasMany(MpnIndikatorCapaian::class, 'mpn_konteks_id')->orderBy('urutan');
    }
    
    public function pengetahuan(): HasMany
    {
        return $this->hasMany(MpnPengetahuan::class, 'mpn_konteks_id');
    }

    // Helper for authorization
    public function isEditableByOperator(): bool
    {
        return true;
    }
}
