<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MprEvaluasi extends Model
{
    protected $table = 'mpr_evaluasi';

    protected $fillable = [
        'mpr_perubahan_id',
        'hasil',
        'lesson_learned',
        'rekomendasi_solusi',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }
}
