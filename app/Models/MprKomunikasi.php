<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MprKomunikasi extends Model
{
    protected $table = 'mpr_komunikasi';

    protected $fillable = [
        'mpr_perubahan_id',
        'informasi',
        'pengirim',
        'target_audiens',
        'media_komunikasi',
        'periode_komunikasi',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }
}
