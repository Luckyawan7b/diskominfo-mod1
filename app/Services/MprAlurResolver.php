<?php

namespace App\Services;

use App\Models\MprPerubahan;

/**
 * MprAlurResolver
 *
 * Menentukan formulir wajib dari flag tabel referensi klasifikasi & lingkup.
 * Hanya mengembalikan checklist; tidak menyimpan status alur apa pun.
 */
class MprAlurResolver
{
    public function __construct(private readonly MprPerubahan $perubahan) {}

    public static function untuk(MprPerubahan $perubahan): self
    {
        return new self($perubahan);
    }

    public function perluAnalisisTeknis(): bool
    {
        return (bool) ($this->perubahan->lingkup->butuh_analisis_teknis
            && $this->perubahan->klasifikasi->wajib_analisis_dampak);
    }

    public function perluAnalisisOrganisasi(): bool
    {
        return (bool) ($this->perubahan->lingkup->butuh_analisis_organisasi
            && $this->perubahan->klasifikasi->wajib_analisis_dampak);
    }

    public function perluPersetujuan(): bool
    {
        return (bool) $this->perubahan->klasifikasi->wajib_persetujuan;
    }

    /**
     * @return array{analisis_risiko: bool, analisis_organisasi: bool, persetujuan: bool}
     */
    public function checklist(): array
    {
        return [
            'analisis_risiko'     => $this->perluAnalisisTeknis(),
            'analisis_organisasi' => $this->perluAnalisisOrganisasi(),
            'persetujuan'         => $this->perluPersetujuan(),
        ];
    }
}
