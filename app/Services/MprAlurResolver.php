<?php

namespace App\Services;

use App\Models\MprPerubahan;
use Illuminate\Support\Str;

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

    /**
     * Relasi anak per formulir, urut sesuai alur. Dipakai juga untuk withCount di indeks.
     *
     * @var array<string, array{label: string, relasi: string}>
     */
    public const FORMULIR = [
        'analisis_risiko'        => ['label' => 'Analisis Risiko (2A)', 'relasi' => 'analisisRisiko', 'route' => 'mpr.analisis-risiko.form'],
        'analisis_organisasi'    => ['label' => 'Analisis Organisasi (2B)', 'relasi' => 'analisisOrganisasi', 'route' => 'mpr.analisis-organisasi.form'],
        'persetujuan'            => ['label' => 'Persetujuan', 'relasi' => 'persetujuan', 'route' => 'mpr.persetujuan.form'],
        'implementasi'           => ['label' => 'Perencanaan: Implementasi', 'relasi' => 'implementasi', 'route' => 'mpr.perencanaan.form', 'tab' => 'implementasi'],
        'strategi'               => ['label' => 'Perencanaan: Strategi', 'relasi' => 'strategi', 'route' => 'mpr.perencanaan.form', 'tab' => 'strategi'],
        'sumber_daya'            => ['label' => 'Perencanaan: Sumber Daya', 'relasi' => 'sumberDaya', 'route' => 'mpr.perencanaan.form', 'tab' => 'sumber_daya'],
        'indikator_keberhasilan' => ['label' => 'Perencanaan: Indikator Keberhasilan', 'relasi' => 'indikatorKeberhasilan', 'route' => 'mpr.perencanaan.form', 'tab' => 'indikator_keberhasilan'],
        'stakeholder'            => ['label' => 'Perencanaan: Stakeholder', 'relasi' => 'stakeholder', 'route' => 'mpr.perencanaan.form', 'tab' => 'stakeholder'],
        'komunikasi'             => ['label' => 'Perencanaan: Komunikasi', 'relasi' => 'komunikasi', 'route' => 'mpr.perencanaan.form', 'tab' => 'komunikasi'],
        'pelatihan'              => ['label' => 'Perencanaan: Pelatihan', 'relasi' => 'pelatihan', 'route' => 'mpr.perencanaan.form', 'tab' => 'pelatihan'],
        'logbook'                => ['label' => 'Pelaksanaan (Logbook)', 'relasi' => 'logbook', 'route' => 'mpr.logbook.form'],
        'evaluasi'               => ['label' => 'Evaluasi', 'relasi' => 'evaluasi', 'route' => 'mpr.evaluasi.form'],
    ];

    /**
     * Daftar formulir dengan status wajib (dari resolver; formulir tanpa aturan kondisional selalu wajib)
     * dan status terisi (ada baris data anak).
     *
     * @return array<string, array{label: string, wajib: bool, terisi: bool, route: ?string, tab: ?string}>
     */
    public function formulir(): array
    {
        $kondisional = $this->checklist();

        $hasil = [];
        foreach (self::FORMULIR as $kunci => $meta) {
            $hasil[$kunci] = [
                'label'  => $meta['label'],
                'wajib'  => $kondisional[$kunci] ?? true,
                'terisi' => $this->adaData($meta['relasi']),
                'route'  => $meta['route'] ?? null,
                'tab'    => $meta['tab'] ?? null,
            ];
        }

        return $hasil;
    }

    /**
     * Label formulir yang sudah berisi data (untuk pengaman ubah klasifikasi/lingkup).
     *
     * @return list<string>
     */
    public function formulirTerisi(): array
    {
        return collect($this->formulir())->where('terisi', true)->pluck('label')->values()->all();
    }

    /**
     * @return array{terisi: int, wajib: int}
     */
    public function kelengkapan(): array
    {
        $wajib = collect($this->formulir())->where('wajib', true);

        return [
            'terisi' => $wajib->where('terisi', true)->count(),
            'wajib'  => $wajib->count(),
        ];
    }

    /**
     * Memakai withCount bila sudah dimuat (hindari N+1 di indeks), jika tidak query exists().
     */
    private function adaData(string $relasi): bool
    {
        $atribut = Str::snake($relasi) . '_count';

        if (array_key_exists($atribut, $this->perubahan->getAttributes())) {
            return (int) $this->perubahan->getAttribute($atribut) > 0;
        }

        return $this->perubahan->{$relasi}()->exists();
    }
}
