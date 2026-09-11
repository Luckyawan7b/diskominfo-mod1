# Plan Implementasi Tema Dark & Light — SPBE Layanan & Manajemen Risiko

**Untuk:** Coding agent yang melanjutkan pekerjaan di repo Laravel "Manajemen Risiko & Layanan SPBE — Diskominfo"
**Status saat ini:** Aplikasi 100% memakai Tailwind CSS v4 (utility classes) + Alpine.js, hardcoded ke palet `slate-900` + `emerald/teal`. Belum ada mode gelap, belum ada tombol toggle.
**Tujuan pekerjaan ini:** Menambahkan sistem tema dark/light berbasis token warna, memetakan ulang palet lama (`main.css`/`app.js` dari Diskominfo) ke Tailwind v4 `@theme`, dan mengganti seluruh warna hardcoded di Blade menjadi token semantik — **tanpa mengubah struktur komponen/markup** yang sudah berjalan.

---

## 0. Keputusan yang Sudah Difinalkan (jangan tanya ulang ke user)

1. **Tailwind tetap dipakai.** Tidak ada migrasi ke class vanilla CSS (`main.css` lama hanya jadi referensi palet warna, tidak di-`@import` ke aplikasi).
2. **Tema default saat pertama kali buka = light.** Tidak fallback ke `prefers-color-scheme`. Hanya baca `localStorage.theme`; kalau kosong, tetap light.
3. **5 warna modul dashboard belum punya palet resmi dari mitra** → pakai satu keluarga warna (tint bertahap dari primary), tidak dibedakan per-hue. Jangan improvisasi warna baru di luar token yang didefinisikan di §2.
4. **Toggle tema ditulis sebagai Alpine.js component**, bukan mengadopsi `app.js` versi vanilla lama. File `main.css`/`app.js` asli disimpan sebagai arsip referensi saja, **tidak di-load** di aplikasi.
5. Mekanisme dark mode: atribut `data-theme="dark"` di `<html>`, dikontrol lewat Tailwind v4 `@custom-variant dark`. **Tidak** pakai `dark:` prefix manual di tiap elemen — semua warna lewat token semantik (`bg-surface`, `text-muted`, dst) yang otomatis berubah nilai saat atribut berubah.

---

## 1. Fondasi Token Warna (Fase 0 — BLOCKING, kerjakan lebih dulu)

### 1.1 File yang diedit
- `resources/css/app.css`

### 1.2 Isi lengkap `@theme` + dark override

Ganti isi `resources/css/app.css` menjadi:

```css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';

@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';

    /* Brand */
    --color-primary: #293681;
    --color-primary-dark: #232e70;
    --color-primary-light: #3b4aa0;
    --color-accent: #1683ff;

    /* Surface & layout */
    --color-page: #c4e5ff;
    --color-surface: #ffffff;
    --color-surface-soft: #eef8ff;
    --color-surface-raised: #ffffff;
    --color-field: #e5f4ff;
    --color-border: #d4dce5;
    --color-border-strong: #b9c4cf;

    /* Teks */
    --color-text: #171717;
    --color-text-strong: #0b0b0b;
    --color-muted: #6d7784;

    /* Semantik umum */
    --color-success: #12a800;
    --color-success-bg: #e8ffe0;
    --color-danger: #d40006;
    --color-danger-bg: #ffe1e2;
    --color-warning: #a86400;
    --color-warning-bg: #fff3d9;
    --color-info: #1683ff;
    --color-info-bg: #e5f4ff;

    /* Skala risiko resmi — 4 level, dipakai di SEMUA tempat yang menampilkan besaran risiko */
    --color-risk-low: #16a34a;
    --color-risk-low-bg: #dcfce7;
    --color-risk-medium: #ca8a04;
    --color-risk-medium-bg: #fef9c3;
    --color-risk-high: #ea580c;
    --color-risk-high-bg: #ffedd5;
    --color-risk-critical: #dc2626;
    --color-risk-critical-bg: #fee2e2;

    /* Modul dashboard — 1 keluarga warna, tint bertahap dari primary */
    --color-module-1: #293681;
    --color-module-2: #3b4aa0;
    --color-module-3: #4d5db8;
    --color-module-4: #5f70cf;
    --color-module-5: #7183e0;
    --color-module-bg: #eef1fb;
}

@custom-variant dark (&:where([data-theme=dark], [data-theme=dark] *));

[data-theme="dark"] {
    --color-primary: #5b6ae0;
    --color-primary-dark: #4a58c9;
    --color-primary-light: #7c89ea;
    --color-accent: #4aa2ff;

    --color-page: #0f1522;
    --color-surface: #161d2e;
    --color-surface-soft: #1c2438;
    --color-surface-raised: #202a41;
    --color-field: #1c2438;
    --color-border: #2b3550;
    --color-border-strong: #3c4a6b;

    --color-text: #e7ebf3;
    --color-text-strong: #ffffff;
    --color-muted: #99a3b8;

    --color-success: #4ade80;
    --color-success-bg: #113322;
    --color-danger: #ff6166;
    --color-danger-bg: #3a1418;
    --color-warning: #fbbf24;
    --color-warning-bg: #3a2c0c;
    --color-info: #4aa2ff;
    --color-info-bg: #10223a;

    --color-risk-low: #4ade80;
    --color-risk-low-bg: #10331f;
    --color-risk-medium: #facc15;
    --color-risk-medium-bg: #362b08;
    --color-risk-high: #fb923c;
    --color-risk-high-bg: #3a2410;
    --color-risk-critical: #f87171;
    --color-risk-critical-bg: #3a1213;

    --color-module-1: #5b6ae0;
    --color-module-2: #7c89ea;
    --color-module-3: #8b97ee;
    --color-module-4: #9aa4f2;
    --color-module-5: #a9b2f5;
    --color-module-bg: #1c2340;
}
```

> **Catatan implementasi Tailwind v4:** karena `--color-*` didefinisikan di `@theme`, Tailwind otomatis membuat utility `bg-primary`, `text-primary`, `border-primary`, `bg-primary-dark`, `bg-surface`, `text-muted`, `bg-risk-critical-bg`, `text-risk-critical`, dst. **Jangan** menambahkan `dark:` di depan class ini — pergantian nilai terjadi otomatis lewat CSS variable saat atribut `data-theme` berubah.

### 1.3 Script anti-flash (WAJIB, taruh di `<head>` SEBELUM `@vite`)

Tambahkan baris ini persis sebelum tag `@vite(...)` di **ketiga** file layout berikut:
- `resources/views/components/layouts/guest.blade.php`
- `resources/views/components/layouts/hub.blade.php`
- `resources/views/components/layouts/app.blade.php`

```blade
<script>
    (function () {
        var saved = localStorage.getItem('theme');
        if (saved === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    })();
</script>
```

Juga hapus `class="dark"` yang saat ini hardcoded di tag `<html>` ketiga file tersebut (`<html lang="id" class="dark">` → `<html lang="id">`), karena itu peninggalan asumsi "selalu dark" dan bertentangan dengan keputusan default light.

### 1.4 Tombol toggle tema (Alpine component)

Tambahkan markup berikut di:
- `layouts/app.blade.php` — di dalam `.flex.items-center.gap-3` topbar, sebelum form logout.
- `layouts/hub.blade.php` — di dalam `.flex.items-center.gap-4` topbar, sebelum form logout.
- `layouts/guest.blade.php` — pojok kanan atas halaman login (posisi absolute/fixed sederhana), karena user belum login saat butuh ganti tema.

```blade
<button
    type="button"
    x-data="{ dark: document.documentElement.getAttribute('data-theme') === 'dark' }"
    x-init="$watch('dark', value => {
        document.documentElement.setAttribute('data-theme', value ? 'dark' : 'light');
        localStorage.setItem('theme', value ? 'dark' : 'light');
    })"
    @click="dark = !dark"
    :aria-pressed="dark.toString()"
    aria-label="Ganti tema gelap/terang"
    class="p-2 rounded-lg text-muted hover:bg-surface-soft hover:text-text transition-colors cursor-pointer"
>
    <svg x-show="!dark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.02 0l-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <svg x-show="dark" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
    </svg>
</button>
```

### 1.5 QA sebelum lanjut ke Fase 1
Buat halaman uji coba sementara (boleh route dev-only, dihapus setelah selesai) yang menampilkan:
- teks `text-text`, `text-muted`, `text-strong` di atas `bg-page` dan `bg-surface`,
- tombol `bg-primary`, `bg-accent`,
- keempat badge `bg-risk-*-bg text-risk-*`,
- toggle tema aktif di kedua mode.

Cek kontras minimal WCAG AA (4.5:1 untuk teks normal) khususnya `text-muted` di atas `bg-surface` pada kedua tema. **Jangan lanjut ke Fase 1 sebelum ini lolos.**

---

## 2. Fase 1 — Halaman Utama (Login + Daftar Layanan)

### 2.1 File yang diedit
- `resources/views/components/layouts/guest.blade.php`
- `resources/views/livewire/auth/login.blade.php`
- `resources/views/livewire/layanan/layanan-index.blade.php`
- `resources/views/livewire/layanan/partials/layanan-card.blade.php`

### 2.2 Aturan cari-ganti (pola umum, terapkan konsisten)

| Class lama (Tailwind literal) | Ganti ke |
|---|---|
| `bg-slate-900`, `bg-slate-900/80` | `bg-page` |
| `bg-slate-800`, `bg-slate-800/50`, `bg-slate-800/80` | `bg-surface` |
| `bg-slate-800/40`, `bg-slate-900/40`, `bg-slate-900/30` | `bg-surface-soft` |
| `bg-slate-700/50` (field/input) | `bg-field` |
| `border-slate-700/50`, `border-slate-700`, `border-slate-600` | `border-border` |
| `text-white`, `text-slate-200` (judul) | `text-text-strong` |
| `text-slate-300`, `text-slate-400` (isi) | `text-text` |
| `text-slate-400`, `text-slate-500` (sekunder/caption) | `text-muted` |
| `text-emerald-400`, `bg-emerald-500`, `ring-emerald-500`, `focus:ring-emerald-500` | `text-accent`, `bg-accent`, `ring-accent`, `focus:ring-accent` |
| gradient `from-emerald-500 to-teal-600` (tombol utama) | ganti jadi solid `bg-primary hover:bg-primary-dark` (hindari gradient supaya konsisten di dark mode) |
| `bg-emerald-500/10 text-emerald-400 border-emerald-500/20` (badge sukses) | `bg-success-bg text-success` |
| `bg-red-500/10 text-red-400 border-red-500/20` (badge error/dihentikan) | `bg-danger-bg text-danger` |
| `bg-blue-500/10 text-blue-400` (badge info/direncanakan) | `bg-info-bg text-info` |
| `bg-amber-500/10 text-amber-400` (badge prioritas/warning) | `bg-warning-bg text-warning` |

### 2.3 Detail per komponen

**`layanan-card.blade.php`** — variabel `$statusLabel` di bagian `@php` saat ini mapping manual ke class Tailwind (`bg-emerald-500/10 text-emerald-400 border-emerald-500/20`, dst). Ganti isi array jadi:
```php
$statusLabel = match($layanan->status_layanan) {
    'berjalan'     => ['label' => 'Berjalan',     'class' => 'bg-success-bg text-success border-success/20'],
    'direncanakan' => ['label' => 'Direncanakan', 'class' => 'bg-info-bg text-info border-info/20'],
    'dihentikan'   => ['label' => 'Dihentikan',   'class' => 'bg-danger-bg text-danger border-danger/20'],
    default        => ['label' => '-',            'class' => 'bg-surface-soft text-muted border-border'],
};
```
Progress bar: track `bg-border` (bukan `bg-slate-700`), fill `bg-accent` (bukan gradient emerald→teal). Badge prioritas (mahkota) tetap gunakan `bg-warning` solid karena ini badge kecil beraksen kuat — boleh dipertahankan sebagai pengecualian visual, bukan token semantik status.

**`login.blade.php`** — form field `bg-slate-700/50` → `bg-field`, tombol submit gradient → `bg-primary hover:bg-primary-dark`, spinner border tetap `border-white` (oke, tidak perlu token karena selalu di atas `bg-primary`).

---

## 3. Fase 2 — Modul Layanan (Form Deskripsi Layanan + Dashboard 5 Modul)

### 3.1 File yang diedit
- `resources/views/livewire/layanan/layanan-form.blade.php`
- `app/Livewire/Dashboard.php`
- `resources/views/livewire/dashboard.blade.php`

### 3.2 `layanan-form.blade.php`
Terapkan aturan tabel §2.2 pada seluruh field. Sidebar tab AlpineJS (`activeTab`) **tidak perlu diubah strukturnya** — hanya ganti class warna:
- Tab aktif: `bg-emerald-500/10 text-emerald-400 border-emerald-500/50` → `bg-primary/10 text-primary border-primary/50`
- Tab nonaktif: `text-slate-400 hover:bg-slate-800` → `text-muted hover:bg-surface-soft`

### 3.3 `Dashboard.php` — refactor `getModules()`

**Ini perubahan struktur data, bukan cuma Blade.** Method `getModules()` saat ini mengembalikan array dengan key `gradient`, `shadow`, `bg`, `text`, `border` **berbeda-beda tiap modul**. Ganti jadi satu skema seragam memakai token modul (§1.2), dibedakan hanya lewat urutan tint `module-1` s/d `module-5`:

```php
private function getModules(Layanan $layanan, ?MrKonteks $mrKonteks): array
{
    $moduleTint = ['module-1', 'module-2', 'module-3', 'module-4', 'module-5'];

    $items = [
        ['name' => 'Manajemen Risiko', 'description' => '...', 'icon' => 'shield-check', 'active' => true, ...],
        ['name' => 'Manajemen Pengetahuan', 'description' => '...', 'icon' => 'book-open', 'active' => false, ...],
        ['name' => 'Manajemen Perubahan', 'description' => '...', 'icon' => 'arrows-right-left', 'active' => false, ...],
        ['name' => 'Manajemen Keberlangsungan', 'description' => '...', 'icon' => 'arrow-path', 'active' => false, ...],
        ['name' => 'Manajemen Relasi', 'description' => '...', 'icon' => 'users', 'active' => false, ...],
    ];

    foreach ($items as $i => &$item) {
        $item['tint'] = $moduleTint[$i]; // dipakai di Blade: bg-{{ $module['tint'] }}
    }

    return $items;
}
```

Di `dashboard.blade.php`, ganti pemakaian `{{ $module['gradient'] }}`, `{{ $module['shadow'] }}`, `{{ $module['bg'] }}`, `{{ $module['text'] }}`, `{{ $module['border'] }}` menjadi:
```blade
<div class="w-14 h-14 rounded-xl bg-{{ $module['tint'] }} flex items-center justify-center shadow-lg">
    ...
</div>
<h3 class="text-lg font-semibold text-text-strong">{{ $module['name'] }}</h3>
<div class="mt-4 flex items-center gap-1 text-{{ $module['tint'] }} text-sm font-medium opacity-0 group-hover:opacity-100">
```
Card wrapper (border, bg soft) yang tadinya beda tiap modul → seragamkan jadi `border-border bg-module-bg` untuk semua modul aktif, `border-border bg-surface-soft opacity-50` untuk "Segera Hadir" (badge netral, **bukan** warna modul — sesuai keputusan §0.3).

---

## 4. Fase 3 — Modul Manajemen Risiko

### 4.1 File yang diedit (urutan pengerjaan disarankan)
1. `resources/views/livewire/risiko/index.blade.php` (tabel daftar risiko + badge besaran)
2. `resources/views/livewire/risiko/peta.blade.php` (matrix 5×5 — paling sensitif)
3. `resources/views/livewire/konteks/form.blade.php` (4 kartu level selera risiko)
4. `resources/views/livewire/risiko/form.blade.php` (form 5-tab)
5. `resources/views/livewire/pemantauan/form.blade.php`
6. `resources/views/livewire/layanan-digital/index.blade.php`
7. `resources/views/livewire/sasaran/form.blade.php`, `struktur-pelaksana/form.blade.php`

### 4.2 Skala risiko resmi — SATU sumber kebenaran

Buat helper kecil (boleh method di `RiskMatrixCalculator` atau helper Blade) yang memetakan label besaran ke pasangan class token, dipakai **identik** di keempat file (index, peta, konteks form, risiko form):

```php
// tambahkan di App\Services\RiskMatrixCalculator
public function colorClass(string $label): string
{
    return match ($label) {
        'Rendah'        => 'bg-risk-low-bg text-risk-low border-risk-low/30',
        'Sedang'        => 'bg-risk-medium-bg text-risk-medium border-risk-medium/30',
        'Tinggi'        => 'bg-risk-high-bg text-risk-high border-risk-high/30',
        'Sangat Tinggi' => 'bg-risk-critical-bg text-risk-critical border-risk-critical/30',
        default         => 'bg-surface-soft text-muted border-border',
    };
}
```

Ganti semua blok `match(true) { $besaran <= 4 => '...', ... }` yang saat ini duplikat di banyak Blade file (`risiko/index.blade.php`, `risiko/form.blade.php`, `risiko/peta.blade.php`, `admin/review-detail.blade.php`, `pemantauan/form.blade.php`) menjadi pemanggilan `app(RiskMatrixCalculator::class)->colorClass($label)` — ini sekaligus membereskan duplikasi logic warna yang sudah ada di kode lama.

### 4.3 `risiko/peta.blade.php` — Matrix 5×5 (perhatian khusus)

Sel matriks saat ini pakai literal `bg-emerald-600/30 hover:bg-emerald-600/50 border-emerald-500/40 text-emerald-300` dkk berdasarkan `$cell['besaran']`. Ganti `$colorBg` jadi hasil `colorClass()` di atas (tanpa perlu suffix opacity manual karena token bg sudah dirancang punya kontras cukup di kedua tema). Legend warna di bagian atas halaman (4 kotak kecil Rendah/Sedang/Tinggi/Sangat Tinggi) juga ikut pakai token yang sama — pastikan warnanya identik dengan sel matriks, jangan buat set warna legend terpisah.

### 4.4 `konteks/form.blade.php` — 4 kartu level selera risiko

Array `$levels` di `@php` saat ini punya `bgActive`/`bgDefault`/`dotColor` unik per level (emerald/amber/orange/red) — **ini sebenarnya sudah representasi 4 level risiko yang sama**, jadi cukup disamakan literalnya dengan token risk di §1.2 (Rendah→risk-low, Sedang→risk-medium, Tinggi→risk-high, Sangat Tinggi→risk-critical). Tidak perlu ubah logika `$isSelected`.

### 4.5 Hal di luar scope tema tapi ketahuan saat menyentuh file ini

`risiko/index.blade.php` dan `admin/review.blade.php`/`review-detail.blade.php` masih menampilkan badge status (`draft/submitted/approved/rejected`) dari kolom yang **menurut `README.md` sudah dihapus dari database** (lihat bagian "Penghapusan Alur Approval"). Saat mengganti warna badge ini kemungkinan besar akan error karena kolom `status`/`catatan_penolakan` tidak ada lagi di model `MrRisiko`/`MrKonteks` terbaru.

**Tindakan:** jangan asumsikan kolom ini ada. Cek dulu migration/model aktif (`MrRisiko.php`, `MrKonteks.php` di §Schema terbaru) sebelum menyentuh badge status — kemungkinan bagian ini perlu dihapus total dari Blade (bukan sekadar ganti warna), konsisten dengan `isEditableByOperator()` yang sekarang selalu `return true`.

---

## 5. Fase 4 — Admin & Polish Akhir

### 5.1 File yang diedit
- `resources/views/livewire/admin/desa/index.blade.php`
- `resources/views/livewire/admin/user/index.blade.php`
- `resources/views/livewire/admin/review.blade.php`
- `resources/views/components/layouts/app.blade.php` (sidebar nav, breadcrumb)

Terapkan aturan §2.2 yang sama. Sidebar nav aktif state (`bg-emerald-500/10 text-emerald-400`) → `bg-primary/10 text-primary`. Warna per-section admin (`bg-amber-500/10`, `bg-violet-500/10` untuk link Monitoring/Kelola Desa/Kelola User) → seragamkan jadi `bg-primary/10 text-primary` juga (hindari menambah token warna baru untuk sekadar nav highlight).

### 5.2 Checklist audit akhir (jalankan sebagai langkah terakhir)

- [ ] `grep -rn "slate-\|emerald-\|amber-\|violet-\|rose-\|teal-\|blue-\|indigo-" resources/views/` menghasilkan **nol** hasil di luar file yang sengaja dikecualikan (mis. ilustrasi SVG dekoratif `welcome.blade.php` yang bukan bagian aplikasi utama, boleh diabaikan).
- [ ] Toggle tema dites di **setiap** halaman: login, daftar layanan, dashboard 5 modul, form deskripsi layanan, seluruh sub-halaman manajemen risiko, admin.
- [ ] Refresh halaman di mode dark tidak menyebabkan flash putih sekejap (verifikasi script §1.3 terpasang di semua layout).
- [ ] Kontras `text-muted` di atas `bg-surface` dan `bg-field` dicek ulang di dark mode setelah semua halaman selesai (warna token bisa terasa beda di konteks nyata vs halaman uji Fase 0).
- [ ] Badge besaran risiko terlihat identik (warna & label) di keempat tempat: daftar risiko, peta risiko, form risiko, detail review admin.
- [ ] Tidak ada gradient (`from-* to-*`) tersisa di tombol utama — sudah diseragamkan jadi solid `bg-primary`.

---

## 6. Ringkasan Urutan Kerja untuk Agent

```
Fase 0  → app.css (token + dark override) → script anti-flash (3 layout) → tombol toggle (3 layout) → QA kontras
Fase 1  → guest.blade.php, login.blade.php, layanan-index.blade.php, layanan-card.blade.php
Fase 2  → layanan-form.blade.php, Dashboard.php (refactor getModules), dashboard.blade.php
Fase 3  → RiskMatrixCalculator::colorClass() → risiko/index → risiko/peta → konteks/form → risiko/form →
          pemantauan/form → layanan-digital/index → sasaran/form → struktur-pelaksana/form
Fase 4  → admin/desa, admin/user, admin/review, layouts/app.blade.php (sidebar) → audit akhir (grep + manual test)
```

**Aturan tetap berlaku di semua fase:** tidak menambah warna literal baru di luar token §1.2, tidak menulis `dark:` prefix manual, tidak mengubah struktur/markup komponen (Alpine `x-data`, Livewire `wire:model`, dst) kecuali eksplisit diminta di §3.3 dan §4.5.
