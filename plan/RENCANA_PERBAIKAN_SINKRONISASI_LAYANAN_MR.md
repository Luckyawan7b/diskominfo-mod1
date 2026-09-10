# Rencana Perbaikan: Sinkronisasi Kode Aplikasi dengan Skema Database Baru

**Untuk:** Coding agent yang melanjutkan pekerjaan di repo "Sistem Informasi Manajemen Risiko & Layanan SPBE - Diskominfo"
**Sumber kebenaran arsitektur:** `README.md` (sudah menjelaskan perubahan skema database yang RESMI dan FINAL)
**Fokus perbaikan:** Modul **Manajemen Layanan** (`Layanan`) dan **Manajemen Risiko** (`mr_*`)
**Status temuan:** README menjelaskan 3 perubahan besar yang **sudah diterapkan di migration & sebagian model**, tetapi **belum diterapkan secara konsisten di Livewire Components, Blade Views, Middleware, Seeder, dan Tests**. Akibatnya banyak bagian aplikasi akan **error saat runtime** karena memanggil kolom/tabel/relasi yang sudah tidak ada.

---

## 0. Ringkasan Temuan (Gap Analysis)

README menyatakan 4 perubahan resmi:

| # | Perubahan menurut README | Status di Migration/Model | Status di Livewire/Blade/Middleware/Tests |
|---|---|---|---|
| 1 | Tabel `desa` & kolom `desa_id` **dihapus total**, diganti `users.nama_dinas` + `users.alias`, `layanans.unit_pelaksana` | ✅ Migration `layanans` & `mr_konteks` **tidak** punya kolom `desa_id`. `User` model fillable **tidak** punya `desa_id`. | ❌ **Belum** — model `Desa` & tabel `desa` masih dipakai luas: `DesaIndex`, `DesaSeeder`, `Dashboard.php`, `KonteksIndex`, `LayananForm`, `LayananIndex`, `ReviewIndex`, `UserIndex`, `EnsureKonteksEditable`, `EnsureKonteksAccessible`, `EnsureHasLayanan`, hampir semua Blade partial (`layanan-card.blade.php`, `review.blade.php`, dll), dan `ManajemenRisikoWorkflowTest` |
| 2 | Kolom `status` di `mr_konteks` & `mr_risiko` **dihapus**, `catatan_penolakan` **dihapus**, `isEditableByOperator()`/`isApproved()` selalu `true` | ✅ Migration `mr_konteks` & `mr_risiko` **tidak** punya kolom status/catatan_penolakan. Model `MrKonteks` & `MrRisiko` sudah benar (`isEditableByOperator()` → `true`). | ❌ **Belum** — `SubmitKonteks`, `SubmitLayanan`, `KonteksIndex` (`filterStatus`), `ReviewIndex` (`filterStatus`), semua Blade (`konteks/index.blade.php`, `admin/review.blade.php`, `admin/review-detail.blade.php`, `risiko/index.blade.php`) masih baca/tulis kolom `status` & `catatan_penolakan` yang **sudah tidak ada di DB** → akan lempar `SQLSTATE` error |
| 3 | `ref_kategori_risiko_id` (dropdown) → `kategori_risiko` (input teks bebas) | ✅ Migration `mr_risiko` sudah `string('kategori_risiko')`, tabel `ref_kategori_risiko` **tidak ada** di daftar 17 migration bersih. | ❌ **Belum** — `RisikoForm.php` masih pakai properti `ref_kategori_risiko_id`, query `RefKategoriRisiko::orderBy('id')->get()`, dan `save()` masih menulis `ref_kategori_risiko_id` (bukan `kategori_risiko`) sehingga data kategori **tidak pernah tersimpan**. Blade `risiko/form.blade.php` & `risiko/index.blade.php` masih render `$r->kategoriRisiko?->nama_kategori` — relasi ini **tidak ada** di model `MrRisiko`. Model `RefKategoriRisiko` & `RefKategoriRisikoSeeder` masih ada (orphan) |
| 4 | `prioritas_risiko` dihitung otomatis via Observer | ✅ Sudah konsisten, tidak ada masalah | ✅ Tidak perlu diubah |

**Kesimpulan:** Prioritas utama BUKAN menulis kolom baru di database (sudah selesai), melainkan **menghapus / mengganti kode aplikasi yang masih mengacu ke struktur lama**, supaya kode konsisten dengan `README.md` dan tidak crash.

---

## 1. Keputusan Desain yang Perlu Diambil Dulu (sebelum coding)

README belum menjelaskan secara eksplisit **bagaimana cara scoping data per-Dinas** setelah `desa_id` dihapus. ada 2 opsi, agent perlu memilih salah satu (disarankan Opsi A untuk konsistensi dengan pola "1 user = 1 entitas" yang sudah ada di seeder):

- **Opsi A (disarankan): Scoping via `created_by` / `auth()->id()`.**
  Operator hanya melihat `Layanan` miliknya sendiri (`created_by = auth()->id()`), bukan per-dinas. Cocok karena `UserSeeder` memang membuat 1 akun operator per dinas (`operator.diskominfo@...`, `operator.bappeda@...`, dst) — jadi secara de facto scoping per-user = scoping per-dinas.
  Admin tetap melihat semua data, ditambah label `nama_dinas`/`alias` dari relasi `creator`/`user` untuk keperluan tampilan (bukan filter keamanan).

- **Opsi B: Scoping via `users.nama_dinas` (string match).**
  Lebih rapuh (typo pada `nama_dinas` bisa memisahkan data yang seharusnya satu dinas), tidak disarankan kecuali ada requirement "banyak operator dalam 1 dinas melihat data yang sama".

> **Agent harus mengonfirmasi opsi ini** (atau menetapkan default ke Opsi A jika tidak ada instruksi lain) sebelum mulai refactor, karena ini memengaruhi semua middleware & query filter.

---

## 2. Fase Perbaikan — Modul Manajemen Layanan

### Fase L1 — Bersihkan ketergantungan ke `Desa`/`desa_id`
1. `app/Models/Desa.php` — **hapus** (atau pertahankan hanya jika ada tabel `desa` yang masih dipertahankan sengaja; berdasarkan README, tabel ini sudah tidak ada di 17 migration bersih → model ini **orphan**, harus dihapus).
2. `database/seeders/DesaSeeder.php` — **hapus**, dan hapus pemanggilannya dari `DatabaseSeeder.php` jika ada (saat ini tidak terlihat dipanggil — cek ulang).
3. `app/Livewire/Admin/Desa/DesaIndex.php` + view `resources/views/livewire/admin/desa/index.blade.php` — **hapus** (fitur "Kelola Desa" sudah tidak relevan). Hapus juga route `admin.desa.index` di `routes/web.php` dan link "Kelola Desa" di `components/layouts/app.blade.php`.
4. `App\Livewire\Layanan\LayananForm::save()` — hapus baris `$validatedData['desa_id'] = auth()->user()->desa_id;`, ganti dengan `created_by` saja (sudah ada) dan (opsional) `unit_pelaksana` dari `auth()->user()->nama_dinas` jika field ini memang dimaksudkan diisi otomatis dari profil user.
5. `App\Livewire\Layanan\LayananIndex::render()` — ganti `Layanan::where('desa_id', $user->desa_id)` menjadi `Layanan::where('created_by', $user->id)` (Opsi A). Untuk admin, hapus `->with('desa')` (relasi tidak ada), ganti dengan `->with('creator')` bila perlu menampilkan nama dinas pembuat.
6. `resources/views/livewire/layanan/partials/layanan-card.blade.php` — ganti `$layanan->desa?->nama_desa` menjadi `$layanan->creator?->nama_dinas` (atau `alias`).
7. `App\Models\Layanan` — tambahkan relasi `creator()` (sudah ada) — pastikan dipakai sebagai pengganti relasi `desa()` yang tidak ada di model ini.
8. `App\Http\Middleware\EnsureHasLayanan` — ganti `Layanan::where('desa_id', $user->desa_id)->exists()` menjadi `Layanan::where('created_by', $user->id)->exists()`.

### Fase L2 — Sinkronkan form dengan skema kolom `layanans`
1. Cek ulang seluruh field di `LayananForm.php`/`layanan-form.blade.php` terhadap kolom migration `2025_01_01_000001_create_layanans_table.php`. Perhatikan:
   - `enum('target_pengguna', ['publik/masyarakat', 'internal pemerintahan'])` di migration **huruf kecil semua**, sedangkan option di Blade `<option value="Publik/Masyarakat">` dan `<option value="Internal Pemerintahan">` — **case mismatch**, akan gagal validasi/insert pada driver DB yang case-sensitive untuk enum (MySQL) atau constraint check (jika nanti dipindah ke Postgres/Oracle sesuai `Schema ERD Data modeler.sql`). Samakan casing di kedua sisi (disarankan ikut migration: huruf kecil, lalu format tampilan di Blade dengan `ucfirst`/`Str::title` saat display).
   - `sifat_data` sudah konsisten (huruf kecil di keduanya) — jadikan pola acuan untuk memperbaiki `target_pengguna`.
   - Field `unit_pelaksana` **ada di kolom DB tapi tidak ada input-nya** di form Livewire — putuskan: tampilkan sebagai field baru di tab "Identitas Layanan", atau isi otomatis dari `auth()->user()->nama_dinas` di `save()`.
2. Tambahkan validasi rule untuk `unit_pelaksana` jika dijadikan input manual.

### Fase L3 — Review alur Submit/Kunci Layanan
`App\Livewire\Layanan\SubmitLayanan` saat ini masih menulis `mr_konteks.update(['status' => 'approved'])` dan `mr_konteks->risiko()->...->update(['status' => 'approved'])` — **kolom `status` sudah tidak ada**, ini akan melempar query error begitu tombol "Kunci & Selesaikan Layanan" diklik.

Sesuai README poin 2 ("data bersifat langsung final, tapi selalu bisa diubah — CRUD biasa, admin tidak revisi/approve di sistem"), **seluruh konsep "submit/lock/approve" ini sudah tidak relevan lagi**. Rekomendasi:
1. **Hapus** `App\Livewire\Layanan\SubmitLayanan.php` dan view `livewire/layanan/submit-layanan.blade.php` sepenuhnya, atau
2. Jika mitra tetap ingin tombol "Tandai Selesai" sebagai indikator visual saja (bukan mengunci data), tambahkan kolom baru yang eksplisit untuk ini (misal `layanans.status_pengisian` enum `draft`/`lengkap` — **bukan** field approval), dan hilangkan semua logika yang menyentuh `mr_konteks.status`/`mr_risiko.status`.
3. Hapus juga referensi ke `SubmitLayanan` dan `livewire:layanan.submit-layanan` di `resources/views/livewire/dashboard.blade.php` bila komponen ini dihapus.

> **Perlu konfirmasi ke user/mitra** (jangan diasumsikan sepihak) apakah tombol "Kunci Layanan" masih dibutuhkan dalam bentuk lain, karena ini keputusan produk, bukan cuma teknis.

---

## 3. Fase Perbaikan — Modul Manajemen Risiko

### Fase R1 — Hapus alur approval/status di seluruh chain Konteks → Risiko
1. `App\Livewire\Konteks\SubmitKonteks.php` — **hapus komponen ini** (submit ke admin sudah tidak berlaku). Cari & hapus pemanggilannya (`<livewire:konteks.submit-konteks .../>`) di `resources/views/livewire/risiko/index.blade.php`.
2. `App\Livewire\Konteks\KonteksIndex.php`:
   - Hapus properti `$filterStatus` dan filter `$query->where('status', $this->filterStatus)` (kolom tidak ada).
   - Hapus filter `$filterDesa` / `Desa::orderBy('nama_desa')->get()` → ganti dengan filter berbasis dinas (`users.nama_dinas` distinct) bila admin butuh filter, atau hapus filter tersebut sepenuhnya jika scoping sekarang per-`created_by`/per-Layanan.
   - Method `createKonteks()` (mode admin manual) masih membuat `MrKonteks` dengan `'desa_id' => $desaId` — **hapus baris ini** (kolom tidak ada), dan evaluasi apakah method ini masih relevan sama sekali — dengan model baru (1 Layanan = 1 Konteks, dibuat otomatis via `ensureKonteksForLayanan()`), method manual "Buat Konteks Baru" oleh admin kemungkinan besar **sudah usang** dan bisa dihapus bersama `showCreateModal` UI-nya.
3. `App\Livewire\Admin\ReviewIndex.php` & `App\Livewire\Admin\ReviewDetail.php`:
   - Hapus `$filterStatus`, `$query->where('status', ...)`.
   - Hapus `Desa::orderBy('nama_desa')->get()` untuk `desaList`.
   - `MrKonteks::with(['desa', 'risiko', 'layanan'])` — relasi `desa()` **tidak ada** di model `MrKonteks` (hanya ada `layanan()`, `creator()`, dst) → akan error. Ganti dengan `with(['layanan.creator', 'risiko'])`.
4. Blade `admin/review.blade.php` & `admin/review-detail.blade.php`:
   - Hapus seluruh blok `$statusConfig`/`$statusLabel`/badge status (`Draft`, `Menunggu`, `Selesai`, `Ditolak`) — tidak ada lagi field untuk itu.
   - Ganti `$item->desa->nama_desa` → `$item->layanan->creator->nama_dinas` (atau expose accessor di model `Layanan`/`MrKonteks` supaya blade lebih bersih, mis. `MrKonteks::namaDinasAttribute()`).
   - Hapus blok `catatan_penolakan` di `review-detail.blade.php` (sudah dihapus di README poin 2, dan `risiko/index.blade.php` juga masih render `$r->catatan_penolakan` — hapus juga di sana).
5. Blade `konteks/index.blade.php` — hapus filter status, hapus kolom badge status, hapus referensi `$item->desa->nama_desa` → ganti pola sama seperti poin 4.

### Fase R2 — Perbaiki kategori risiko (dropdown → teks bebas)
1. `App\Livewire\Risiko\RisikoForm.php`:
   - Ganti `public ?int $ref_kategori_risiko_id = null;` → `public string $kategori_risiko = '';`
   - Hapus `use App\Models\RefKategoriRisiko;` dan `'kategoriList' => RefKategoriRisiko::orderBy('id')->get()` di `render()`.
   - Di `fillFromRisiko()`: ganti `$this->ref_kategori_risiko_id = $r->ref_kategori_risiko_id;` → `$this->kategori_risiko = $r->kategori_risiko ?? '';`
   - Di `save()`: ganti validasi `'ref_kategori_risiko_id' => 'nullable|exists:ref_kategori_risiko,id'` → `'kategori_risiko' => 'nullable|string|max:191'`; ganti key data `'ref_kategori_risiko_id' => $this->ref_kategori_risiko_id` → `'kategori_risiko' => $this->kategori_risiko`.
2. Blade `risiko/form.blade.php` (Tab 2 — Analisis & Evaluasi):
   - Ganti elemen `<select wire:model="ref_kategori_risiko_id">...</select>` (loop `$kategoriList`) menjadi `<input type="text" wire:model="kategori_risiko" placeholder="Contoh: Risiko Operasional, Risiko Keamanan Informasi, dst">` (input bebas, boleh diberi datalist dari kategori yang sudah pernah dipakai di konteks yang sama, sebagai UX-helper opsional — bukan constraint database).
3. Blade `risiko/index.blade.php` & `admin/review-detail.blade.php`:
   - Ganti `$r->kategoriRisiko?->nama_kategori` → `$r->kategori_risiko`.
4. `app/Models/MrRisiko.php` — pastikan **tidak** ada relasi `kategoriRisiko()` yang mengarah ke tabel yang tidak ada (dari kode yang di-share, memang belum ada — baik, tinggal pastikan tidak ada file lain yang menambahkannya lagi).
5. **Hapus** `app/Models/RefKategoriRisiko.php` dan `database/seeders/RefKategoriRisikoSeeder.php` (orphan, tabel tidak ada). Cek `DatabaseSeeder.php` — pastikan seeder ini tidak dipanggil (dari kode yang ada saat ini, sepertinya sudah tidak dipanggil — verifikasi ulang).

### Fase R3 — Middleware & akses berbasis dinas
1. `App\Http\Middleware\EnsureKonteksEditable`:
   - Karena `isEditableByOperator()` sekarang selalu `true`, blok "Operator hanya bisa edit jika draft/rejected" sudah tidak relevan secara logis, tapi middleware ini masih berguna untuk cek kepemilikan. Ganti:
     ```php
     if ($konteks->desa_id !== $user->desa_id) {
         abort(403, ...);
     }
     ```
     menjadi cek kepemilikan via Layanan, misal:
     ```php
     if ($konteks->layanan->created_by !== $user->id) {
         abort(403, 'Anda tidak memiliki akses ke dokumen dinas lain.');
     }
     ```
   - Nama middleware/pesan error boleh disesuaikan ("desa lain" → "dinas lain" / "perangkat daerah lain").
2. `App\Http\Middleware\EnsureKonteksAccessible` — sama, ganti `$konteks->desa_id === $user->desa_id` → cek via `$konteks->layanan->created_by === $user->id`.
3. Semua Livewire component yang membangun `$availableKonteks` via `MrKonteks::where('desa_id', $user->desa_id)` (`SasaranForm`, `StrukturPelaksanaForm`, `RisikoIndex`, `RisikoForm`, `PetaRisiko`, `PemantauanForm`, `LayananDigitalIndex`, `KonteksForm`) — **semua** perlu diganti pola query-nya menjadi berbasis kepemilikan Layanan, misal:
   ```php
   MrKonteks::whereHas('layanan', fn ($q) => $q->where('created_by', $user->id))
       ->orderByDesc('tahun_penilaian')->get();
   ```
   > Ini adalah pola berulang di banyak file — pertimbangkan membuat 1 method reusable, misal `MrKonteks::scopeAccessibleBy($query, $user)` sebagai local scope di model, supaya tidak duplikasi logic di 7+ file.

### Fase R4 — Tabel & UI yang menampilkan "nama desa"
Ganti seluruh tampilan `$konteks->desa->nama_desa` di:
- `components/layouts/app.blade.php` (breadcrumb sidebar "MODE B: Ada Konteks Aktif")
- `konteks/index.blade.php`
- `admin/review.blade.php`, `admin/review-detail.blade.php`

menjadi kombinasi `nama_instansi` (sudah ada di `mr_konteks`, diisi otomatis dari `nama_dinas` saat `ensureKonteksForLayanan()`) dan/atau `$konteks->layanan->creator->alias`.

---

## 4. Fase Perbaikan — Data Layer Pendukung

1. `App\Livewire\Konteks\KonteksIndex::ensureKonteksForLayanan()` sudah cukup baik (pakai `$layanan->desa->nama_desa ?? ''` untuk `nama_instansi` — **perbaiki**: `$layanan->desa` tidak ada, ganti ke `$layanan->creator->nama_dinas ?? ''`).
2. `App\Livewire\Dashboard.php::mount()` — cek `$user->isOperator() && $layanan->desa_id !== $user->desa_id` → ganti ke `$layanan->created_by !== $user->id`.
3. `App\Livewire\Admin\User\UserIndex.php` — form ini masih 100% berbasis `desa_id`/`Desa::orderBy('nama_desa')` untuk assignment operator ke "desa". Perlu diputuskan ulang: apakah admin masih perlu meng-assign operator ke sebuah entitas Dinas (mis. field `nama_dinas`/`alias` diisi manual saat create user, bukan pilih dari tabel `desa` yang sudah tidak ada), atau field ini dihapus dan setiap user cukup mengisi `nama_dinas`/`alias` sendiri sebagai teks bebas saat dibuat admin. Sesuaikan form (`admin/user/index.blade.php`) — ganti `<select wire:model="desa_id">` → `<input type="text" wire:model="nama_dinas">` + `<input type="text" wire:model="alias">`, hapus constraint unique `desa_id`.

---

## 5. Fase Perbaikan — Tests

`tests/Feature/ManajemenRisikoWorkflowTest.php` dan `tests/Feature/SasaranFormTest.php` masih membuat data dengan `desa_id`, memeriksa `$konteks->status`, `MrKonteks::create([... 'desa_id' => ..., 'status' => 'submitted'])`, dsb. Setelah Fase L & R selesai:
1. Hapus semua `desa_id` dari factory/seed data di test, ganti dengan pola `created_by`.
2. Hapus assertion terhadap `status` (`assertSoftDeleted`, dsb yang menyertakan `status`).
3. Tambahkan test baru khusus untuk:
   - Operator A tidak bisa mengakses `Layanan`/`MrKonteks` milik Operator B (menggantikan test lintas-desa lama).
   - Field `kategori_risiko` tersimpan sebagai teks bebas (bukan lagi via FK).
   - Alur "isi Layanan → 5 modul dashboard" tetap CRUD biasa tanpa status apapun (tidak ada assertion `approved`/`submitted`).

---

## 6. Urutan Eksekusi yang Disarankan

1. **Konfirmasi keputusan desain §1** (scoping per `created_by` vs `nama_dinas`) ke user/mitra sebelum mulai.
2. **Fase L1–L3** (Manajemen Layanan) — modul ini akar dari modul lain, harus stabil lebih dulu.
3. **Fase R1–R4** (Manajemen Risiko) — karena `mr_konteks` bergantung pada `Layanan` yang sudah diperbaiki di langkah 2.
4. **Fase 4** (Data layer pendukung: Dashboard, UserIndex).
5. **Fase 5** (Tests) — jalankan `php artisan test` di setiap sub-fase untuk mendeteksi regresi sedini mungkin, bukan hanya di akhir.
6. Terakhir: cari sisa referensi dengan grep global sebelum dianggap selesai:
   ```bash
   grep -rn "desa_id\|Desa::\|->desa\b\|'status'\|catatan_penolakan\|ref_kategori_risiko\|kategoriRisiko" app resources routes tests
   ```
   Setiap hasil yang muncul di luar file yang memang sengaja dipertahankan (dokumentasi historis, `Schema ERD Data modeler.sql`) harus ditinjau ulang.

---

## 7. Definition of Done

- [ ] `php artisan test` lulus 100% tanpa error SQL terkait kolom `desa_id`, `status`, `catatan_penolakan`, `ref_kategori_risiko_id`
- [ ] Tidak ada lagi pemanggilan `Desa::` atau relasi `->desa` di kode aplikasi (kecuali dokumentasi historis)
- [ ] Form input risiko memakai `kategori_risiko` sebagai teks bebas, tersimpan & tampil dengan benar
- [ ] Tidak ada lagi UI/logic "submit ke admin", badge status (`Draft`/`Submitted`/`Approved`/`Rejected`), atau catatan penolakan di modul Layanan maupun Manajemen Risiko
- [ ] Operator hanya bisa melihat/mengedit `Layanan` & `MrKonteks` miliknya sendiri (sesuai keputusan §1); admin bisa melihat semua
- [ ] `UserIndex` (admin) bisa membuat/edit user tanpa bergantung pada tabel `desa` yang sudah dihapus
- [ ] README.md tetap menjadi acuan tunggal — jika ada perubahan desain baru selama proses ini, README diperbarui juga agar tidak terjadi drift lagi di masa depan
