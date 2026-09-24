<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MpnPengumpulan extends Model
{
    use SoftDeletes;

    protected $table = 'mpn_pengumpulan';

    protected $fillable = [
        'mpn_pengetahuan_id',
        'revisi_dari_id',
        'id_pengetahuan',
        'tanggal_pengumpulan',
        'unit_pengumpulan',
        'lokasi_penyimpanan_lain',
        'keterangan_lokasi_lainnya',
        'status_publikasi_simpan',
        'visibilitas_dokumen',
        'ref_metode_pengolahan_id',
        'deskripsi_pengolahan',
        'tanggal_update_terakhir',
        'rating_pengetahuan',
        'penulis',
        'label_tags',
        'kontributor',
        'url'
    ];

    public function pengetahuan(): BelongsTo
    {
        return $this->belongsTo(MpnPengetahuan::class, 'mpn_pengetahuan_id');
    }

    public function metodePengolahan(): BelongsTo
    {
        return $this->belongsTo(RefMetodePengolahan::class, 'ref_metode_pengolahan_id');
    }

    public function revisiDari(): BelongsTo
    {
        return $this->belongsTo(self::class, 'revisi_dari_id');
    }

    public function revisi(): HasMany
    {
        return $this->hasMany(self::class, 'revisi_dari_id');
    }

    public function pemanfaatan(): HasMany
    {
        return $this->hasMany(MpnPemanfaatan::class, 'mpn_pengumpulan_id');
    }

    public function alihPengetahuan(): HasMany
    {
        return $this->hasMany(MpnAlihPengetahuan::class, 'mpn_pengumpulan_id');
    }
}
