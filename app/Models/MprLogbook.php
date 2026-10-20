<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class MprLogbook extends Model
{
    protected $table = 'mpr_logbook';

    protected $fillable = [
        'mpr_perubahan_id',
        'unit_pelaksana',
        'pic_pelaksana',
        'tanggal_mulai',
        'tanggal_selesai',
        'catatan',
        'bukti_url',
        'status_perubahan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }

    public function lampiran(): MorphMany
    {
        return $this->morphMany(MrLampiran::class, 'lampirable');
    }

    public function bersihkanLampiran(): void
    {
        $lampiranList = MrLampiran::withTrashed()
            ->where('lampirable_type', self::class)
            ->where('lampirable_id', $this->id)
            ->get();

        foreach ($lampiranList as $lampiran) {
            if ($lampiran->path_file) {
                Storage::disk('public')->delete($lampiran->path_file);
            }
            $lampiran->forceDelete();
        }
    }

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            $model->bersihkanLampiran();
        });
    }
}
