<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpnPemanfaatan extends Model
{
    protected $table = 'mpn_pemanfaatan';

    protected $fillable = [
        'mpn_pengumpulan_id',
        'tanggal_pemanfaatan',
        'jenis_pengguna',
        'unit_pengguna',
        'tujuan_pemanfaatan',
        'rating',
        'created_by'
    ];

    public function pengumpulan(): BelongsTo
    {
        return $this->belongsTo(MpnPengumpulan::class, 'mpn_pengumpulan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
