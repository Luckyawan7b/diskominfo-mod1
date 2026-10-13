<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MprStakeholder extends Model
{
    protected $table = 'mpr_stakeholder';

    protected $fillable = [
        'mpr_perubahan_id',
        'nama_stakeholder',
        'ref_kategori_stakeholder_id',
        'ref_jenis_resistensi_id',
        'ref_tingkat_resistensi_id',
    ];

    public function perubahan(): BelongsTo
    {
        return $this->belongsTo(MprPerubahan::class, 'mpr_perubahan_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(RefKategoriStakeholder::class, 'ref_kategori_stakeholder_id');
    }

    public function jenisResistensi(): BelongsTo
    {
        return $this->belongsTo(RefJenisResistensi::class, 'ref_jenis_resistensi_id');
    }

    public function tingkatResistensi(): BelongsTo
    {
        return $this->belongsTo(RefTingkatResistensi::class, 'ref_tingkat_resistensi_id');
    }
}
