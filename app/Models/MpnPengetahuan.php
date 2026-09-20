<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MpnPengetahuan extends Model
{
    use SoftDeletes;

    protected $table = 'mpn_pengetahuan';

    protected $fillable = [
        'mpn_konteks_id',
        'nama_sub_fitur',
        'layanan_prioritas',
        'nama_pengetahuan',
        'sudah_terdokumentasi',
        'ref_aspek_pemdi_id',
        'ref_indikator_pemdi_id',
        'apakah_terdokumentasi',
        'created_by'
    ];

    public function konteks(): BelongsTo
    {
        return $this->belongsTo(MpnKonteks::class, 'mpn_konteks_id');
    }

    public function aspekPemdi(): BelongsTo
    {
        return $this->belongsTo(RefAspekPemdi::class, 'ref_aspek_pemdi_id');
    }

    public function indikatorPemdi(): BelongsTo
    {
        return $this->belongsTo(RefIndikatorPemdi::class, 'ref_indikator_pemdi_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rencanaDokumentasi(): HasOne
    {
        return $this->hasOne(MpnRencanaDokumentasi::class, 'mpn_pengetahuan_id');
    }
}
