# Perencanaan Perubahan: Restrukturisasi Alur "Per Layanan" & 5 Modul Manajemen

**Untuk:** Coding agent / tim developer yang melanjutkan pekerjaan di repo Laravel "Manajemen Risiko SPBE Desa/Pemda"
**Status kode saat ini:** Modul **Manajemen Risiko** (1 dari 5 modul) sudah selesai dan berjalan penuh (Konteks → Sasaran → Struktur → Risiko → Layanan Digital → Peta Risiko → Pemantauan), dengan alur admin Review & Approval.
**Pemicu perubahan:** Permintaan mitra untuk mengubah unit analisis dari **"per Desa/OPD per tahun"** menjadi **"per Layanan"**, dan menambahkan tahap pengisian deskripsi layanan sebelum masuk ke 5 modul manajemen.

---

## 1. Ringkasan Permintaan Mitra (dikelompokkan)

| # | Permintaan Mitra | Kategori |
|---|---|---|
| 1 | Login ditambah captcha | Keamanan — future scope |
| 2 | User harus mengisi **Deskripsi Layanan** (sesuai `Rekapitulasi_Data_Layanan.xlsx`) sebelum lanjut | Alur baru — wajib |
| 3 | Deskripsi Layanan harus diisi dulu, baru bisa memilih tampilan 5 manajemen (dashboard saat ini) | Alur baru — wajib |
| 4 | Tiap manajemen punya **Penetapan Konteks** sendiri | Perubahan struktur data |
| 5 | Tiap **layanan** harus mengisi **semua** 5 manajemen | Perubahan struktur data |
| 6 | Satu akun bisa punya **banyak layanan** (deskripsi layanan) | Perubahan relasi data |
| 7 | Homepage menampilkan **daftar layanan prioritas** dulu | Perubahan navigasi utama |
| 8 | Kertas kerja ini **per layanan**, bukan per dinas/OPD | Perubahan unit analisis (breaking change) |
| 9 | Urutan: isi deskripsi layanan → baru isi 5 modul kertas kerja | Alur baru — wajib |
| 10 | Ulasan (review admin) dilakukan **setelah semua modul terisi** | Perubahan trigger review |
| 11 | Dibatasi: **1 perangkat daerah = 1 akun** | Kebijakan akses |
| 12 | **Tidak ada deadline**, dan **tidak ada catatan revisi** ke perangkat daerah | Penyederhanaan approval flow |

---

## 2. Dampak Terhadap Arsitektur Saat Ini

### 2.1 Perubahan unit analisis (paling berdampak)

Saat ini `mr_konteks` adalah akar data, terikat ke `desa_id` + `tahun_penilaian` (`unique(['desa_id','tahun_penilaian'])`). Semua modul risiko (`mr_sasaran_upr`, `mr_struktur_pelaksana`, `mr_risiko`, dst.) menggantung di `mr_konteks_id`.

**Perubahan:** perlu entitas baru **`Layanan`** (deskripsi layanan) sebagai akar baru. Setiap `Layanan`:
- dimiliki oleh 1 `desa_id` (OPD) — banyak layanan boleh dimiliki 1 OPD/akun.
- menjadi induk dari **5 Konteks modul** (satu Konteks per modul manajemen), bukan induk langsung dari `mr_risiko` dkk.

Relasi baru secara konsep:

```
Desa (1 akun operator)
 └─ Layanan (banyak per Desa)      ← BARU: menyimpan 28 kolom deskripsi layanan
     ├─ Konteks Manajemen Risiko        (mr_konteks, existing — tambah kolom layanan_id)
     ├─ Konteks Manajemen Pengetahuan   (BARU, belum ada tabel)
     ├─ Konteks Manajemen Perubahan     (BARU, belum ada tabel)
     ├─ Konteks Manajemen Keberlangsungan (BARU, belum ada tabel — lihat file BCPDRP)
     └─ Konteks Manajemen Relasi        (BARU, belum ada tabel — lihat file MRP)
```

Catatan: file-file di root project (`24072026_PEMDA_MPRManajemen Perubahan...xlsx`, `24072026_Pemda_MPNManajemen Pengetahuan...xlsx`, `24072026_MRPManajemen Relasi Pengguna...xlsx`, `24072026_BCPDRP V.4.2 SimpleVersion.xlsx`) adalah kertas kerja resmi untuk 4 modul lain yang belum diimplementasi — jadi kolom Excel tersebut **jadi acuan skema tabel modul 2–5**, sama seperti `24072026_Contoh_Pengisian_Daerah...xlsx` dulu jadi acuan `mr_*`.

### 2.2 Perubahan makna "unique per periode"

`mr_konteks.unique(['desa_id','tahun_penilaian'])` mengasumsikan 1 dokumen per OPD per tahun. Dengan model baru, **1 dokumen kertas kerja MR = 1 Layanan** (bukan 1 OPD). Migrasi harus:
- Menambah kolom `layanan_id` (nullable dulu untuk kompatibilitas migrasi data lama, lalu di-nonnull-kan).
- Constraint unique berubah menjadi `unique(['layanan_id'])` (1 layanan = 1 konteks MR aktif) — tahun penilaian tetap disimpan sebagai metadata tapi tidak lagi jadi bagian dari unique key, karena tidak ada siklus/deadline tahunan yang mengikat.

### 2.3 Penyederhanaan alur approval (poin #10 & #12)

Yang **dihapus/disederhanakan**:
- `catatan_penolakan` (di `mr_risiko` dan flow `ReviewDetail::submitRejectRisk`) — **tidak dipakai lagi** karena tidak ada revisi ke OPD.
- Status `rejected` pada `mr_konteks` dan `mr_risiko` — disederhanakan, kemungkinan cukup `draft` → `submitted` → `approved` (drop `rejected` dari alur, atau dipertahankan di skema tapi tidak dipakai UI-nya).
- Konsep "tenggat waktu" — tidak ada field deadline yang perlu ditambahkan; pastikan tidak ada asumsi implisit soal batas waktu di kode (sejauh ini tidak ada, aman).

Yang **berubah trigger-nya**:
- Submit ke admin (`SubmitKonteks`) saat ini validasi kelengkapan hanya untuk modul Risiko. Ke depan, validasi kelengkapan submit harus mengecek **kelengkapan seluruh 5 modul untuk 1 Layanan**, bukan cuma modul Risiko sendirian.
- "Ulasan" (Review & Approval) sebaiknya pindah level dari **per-Konteks-MR** menjadi **per-Layanan** (1 tombol submit untuk seluruh 5 modul, bukan submit terpisah per modul).

### 2.4 Kebijakan "1 perangkat daerah = 1 akun" (poin #11)

Skema `users` sudah punya `desa_id` (1 user → 1 desa). Yang belum ada: **enforcement 1 desa hanya boleh punya 1 user aktif**. Perlu:
- Unique constraint `users.desa_id` (partial/unique where role=operator, karena admin `desa_id` = null dan boleh banyak admin).
- Validasi di `UserIndex::save()` — tolak assign desa yang sudah punya operator lain.

### 2.5 Homepage baru (poin #7 & #3)

Alur navigasi berubah total:

```
Login
 │
 ▼
[Guard] Apakah user sudah punya ≥1 Layanan (Deskripsi Layanan)?
 │                              │
 NO                             YES
 │                              │
 ▼                              ▼
Wajib isi Form Deskripsi   Homepage baru:
Layanan (min. 1)           Daftar Layanan milik akun ini
 │                         (layanan prioritas ditampilkan
 ▼                          di atas/duluan)
Lanjut ke Homepage               │
                                  ▼
                          Pilih 1 Layanan
                                  │
                                  ▼
                          Dashboard 5 Modul (UI existing
                          Dashboard.php, di-scope ke
                          layanan_id yang dipilih)
                                  │
                                  ▼
                          Tiap modul: jika Konteks modul
                          tsb belum ada → wajib isi
                          Konteks dulu (pola sama seperti
                          MR sekarang)
```

---

## 3. Rencana Perubahan Skema Database

### 3.1 Tabel baru: `layanan` (Deskripsi Layanan)

Diturunkan dari 28 kolom `Rekapitulasi_Data_Layanan.xlsx` (sheet "Rekap Data Layanan"):

| Kolom Excel | Kolom Migrasi (usulan) | Tipe |
|---|---|---|
| Unit Pelaksana | (pakai `desa_id` existing, atau tambah `unit_pelaksana` bila beda granularitas dari OPD) | FK / string |
| Bidang/Bagian | `bidang_bagian` | string nullable |
| Status Layanan | `status_layanan` (enum: berjalan, direncanakan, dihentikan) | enum |
| Nama Layanan | `nama_layanan` | string, required |
| Deskripsi Layanan | `deskripsi_layanan` | text |
| Target Pengguna | `target_pengguna` (enum: Publik/Masyarakat, Internal Pemerintahan) | enum |
| K/L Terkait | `kl_terkait` | string nullable |
| Supplier Data | `supplier_data` | string nullable |
| Nama Data Input | `nama_data_input` | text nullable |
| Nama Data Output | `nama_data_output` | text nullable |
| Sifat Data | `sifat_data` (enum: terbuka, terbatas, tertutup) | enum |
| Jenis Data | `jenis_data` | string nullable |
| Validitas Data | `validitas_data` (enum frekuensi) | enum |
| Interoperabilitas | `interoperabilitas` (Ya/Tidak) | boolean |
| Tujuan Integrasi | `tujuan_integrasi` | text nullable |
| Metode Integrasi | `metode_integrasi` | string nullable |
| Link Dokumen Integrasi | `link_dokumen_integrasi` | string nullable (URL) |
| Nama Aplikasi | `nama_aplikasi` | string nullable |
| Tipe Aplikasi | `tipe_aplikasi` | string nullable |
| Link Aplikasi | `link_aplikasi` | string nullable (URL) |
| Keluaran Aplikasi | `keluaran_aplikasi` | text nullable |
| Letak Server | `letak_server` | string nullable |
| Upload DPA | `link_dpa` | string nullable (URL — pola sama seperti `mr_lampiran`, bisa link GDrive) |
| Tahun Pembuatan | `tahun_pembuatan` | year nullable |
| SLA | `link_sla` | string nullable |
| SOP | `link_sop` | string nullable |
| Helpdesk | `helpdesk` | string nullable |
| — | `is_prioritas` (BARU — untuk kebutuhan homepage §2.5) | boolean default false |
| — | `desa_id`, `created_by`, timestamps, softDeletes | FK / meta |

> Catatan desain: kolom sangat banyak dan sebagian besar teknis/opsional. Sarankan form multi-section (mis. accordion: "Identitas Layanan", "Data & Integrasi", "Aplikasi & Infrastruktur", "Dokumen Pendukung") mengikuti pola UX yang sudah dipakai di `konteks/form.blade.php` — jangan bentuk 1 tabel panjang seperti spreadsheet aslinya (sudah jadi pelajaran dari `KONTEKS_PERUBAHAN_SASARAN_UPR.md`, hindari mengulang kesalahan struktur "flat" yang membingungkan operator awam teknologi).

### 3.2 Perubahan tabel existing

- `mr_konteks`: tambah `foreignId('layanan_id')->constrained('layanan')`. Drop unique lama `['desa_id','tahun_penilaian']`, ganti `unique(['layanan_id'])`. `desa_id` bisa dipertahankan (denormalized) untuk mempermudah query filter admin per OPD, atau diakses via `layanan.desa_id`.
- `mr_risiko`: **hapus penggunaan** `catatan_penolakan` dari UI (kolom di DB boleh tetap ada untuk kompatibilitas, tapi tidak lagi diisi/ditampilkan).
- `users`: tambah unique index untuk `desa_id` khusus role operator (lihat §2.4).

### 3.3 Tabel baru untuk 4 modul lain (garis besar, detail menyusul per modul)

Berdasarkan file yang sudah ada di project:
- **Manajemen Perubahan** (`mpr_konteks`, dst.) — acuan: `24072026_PEMDA_MPRManajemen Perubahan Layanan Digital Formulir.xlsx`.
- **Manajemen Pengetahuan** (`mpn_konteks`, dst.) — acuan: `24072026_Pemda_MPNManajemen Pengetahuan Contoh Pengisian.xlsx`.
- **Manajemen Relasi Pengguna** (`mrp_konteks`, dst.) — acuan: `24072026_MRPManajemen Relasi Pengguna Contoh Pengisian Pemda.xlsx`.
- **Manajemen Keberlangsungan (BCP/DRP)** (`bcpdrp_konteks`, dst.) — acuan: `24072026_BCPDRP V.4.2 SimpleVersion.xlsx`.

Setiap modul disarankan punya pola konsisten: 1 tabel `..._konteks` dengan `layanan_id` unik, lalu tabel turunan sesuai formulir masing-masing (mengikuti pola `mr_sasaran_upr` → `mr_indikator_kinerja`, dst.). **Ini pekerjaan terpisah** (butuh sesi analisis Excel per modul) — di luar cakupan perubahan alur ini, tapi struktur `Layanan` harus disiapkan agar modul-modul ini tinggal "colok" `layanan_id` yang sama.

---

## 4. Perubahan Alur Aplikasi & Halaman

### 4.1 Middleware/guard baru

Tambahkan middleware `EnsureHasLayanan` (pola serupa `EnsureKonteksEditable`):
- Jika user login dan **belum punya `layanan` sama sekali** → redirect paksa ke `layanan.create` (form Deskripsi Layanan), tidak bisa mengakses rute lain kecuali logout.
- Berlaku untuk role operator saja (admin tidak butuh Layanan).

### 4.2 Route baru (usulan)

```php
// Deskripsi Layanan — gantikan posisi "dashboard hub" sebagai pintu masuk pertama
Route::get('/layanan', LayananIndex::class)->name('layanan.index');       // Homepage baru: daftar layanan (prioritas dulu)
Route::get('/layanan/baru', LayananForm::class)->name('layanan.create');  // Form isi Deskripsi Layanan
Route::get('/layanan/{layanan}', LayananForm::class)->name('layanan.edit');
Route::get('/layanan/{layanan}/manajemen', Dashboard::class)->name('layanan.dashboard'); // 5-modul dashboard existing, di-scope ke layanan
```

Semua route modul existing (`konteks.form`, `sasaran.form`, dst.) tetap memakai `{konteks}` sebagai binding, tapi **konteks selalu dibuat/diambil by `layanan_id`**, bukan langsung dari route `desa`. Praktisnya: route prefix `manajemen-risiko/konteks/{konteks}` tetap sama secara URL, hanya sumber pembuatan `MrKonteks` (di `LayananDashboard`/`Dashboard.php`) yang berubah dari "buat per desa+tahun" menjadi "buat/ambil per layanan".

### 4.3 Perubahan `Dashboard.php` (5-modul hub)

- Terima parameter `{layanan}`.
- Badge count & status tiap modul dihitung per `layanan_id`, bukan global per role.
- Klik salah satu dari 5 kartu modul → jika Konteks modul tsb untuk layanan ini belum ada, buat otomatis (pola sama seperti `KonteksIndex::createKonteks()` sekarang, tapi trigger-nya saat pertama kali membuka modul, bukan tombol "Buat Konteks Baru" terpisah) → langsung masuk ke form Konteks modul tsb.

### 4.4 Homepage baru — `LayananIndex`

- Tabel/daftar kartu Layanan milik akun (operator) yang login.
- **Layanan prioritas** (`is_prioritas = true`) ditampilkan di bagian atas/terpisah, sesuai poin #7.
- Setiap kartu menunjukkan progres 5 modul (mis. "3/5 modul terisi") + status keseluruhan (draft/submitted/approved).
- Tombol "+ Tambah Layanan Baru" → ke `layanan.create`.
- Klik kartu → `layanan.dashboard`.

### 4.5 Perubahan alur submit & review (poin #10, #12)

- `SubmitKonteks` (existing, per-modul-MR) → digantikan komponen baru **`SubmitLayanan`** yang:
  - Validasi kelengkapan **kelima modul** untuk `layanan_id` tsb (bukan cuma MR).
  - Set status "submitted" di level Layanan (tambah kolom `layanan.status` bila diperlukan, atau status agregat dihitung dari status ke-5 konteks modul).
- `ReviewIndex`/`ReviewDetail` (existing, admin) → daftar yang direview berubah jadi **daftar Layanan berstatus submitted** (bukan daftar `mr_konteks` submitted), detail review menampilkan ringkasan seluruh 5 modul dalam satu halaman.
- **Hapus** modal "Tolak" (`openRejectModal`, `submitRejectRisk`, field `catatan_penolakan` di form/tampilan) — ganti jadi hanya tombol **Approve** (single decision, tanpa jalur reject+catatan). Jika mitra tetap ingin opsi "tolak" tanpa catatan, cukup approve/tidak-approve tanpa teks revisi — perlu konfirmasi ke mitra apakah reject dihapus total atau hanya catatannya yang dihapus (rekomendasi: tanya balik sebelum implementasi, karena requirement "catatan revisi tidak ada" bisa berarti keduanya).

### 4.6 Login + captcha (future scope, poin #1)

Ditandai eksplisit "fitur masa depan" oleh mitra — **tidak perlu dikerjakan sekarang**. Catatan implementasi untuk nanti: tambahkan field captcha di `livewire/auth/login.blade.php` + validasi di `Login::authenticate()` (misalnya pakai package captcha atau reCAPTCHA v3 external service). Simpan sebagai item backlog, jangan blocking rilis modul Layanan.

---

## 5. Kebijakan Akses & Data Existing

### 5.1 Migrasi data lama

Karena `mr_konteks` sekarang wajib py`layanan_id`, dan data existing (jika sudah ada di dev/staging) hanya mengenal `desa_id`+`tahun_penilaian`:
1. Buat migration yang, untuk tiap `mr_konteks` lama, **auto-generate 1 `layanan` placeholder** ("Layanan belum diberi nama — migrasi otomatis") milik `desa_id` yang sama, lalu isi `mr_konteks.layanan_id` dengan id layanan placeholder tsb.
2. Operator diminta melengkapi Deskripsi Layanan untuk placeholder tsb saat login berikutnya (guard `EnsureHasLayanan` akan otomatis mendeteksi placeholder ini sebagai "belum lengkap" jika kita tandai field wajib minimal, misal `nama_layanan` kosong/placeholder).
3. **Backup database sebelum migrasi** — pola sama seperti catatan di `KONTEKS_PERUBAHAN_SASARAN_UPR.md` §5 (migration data satu arah, tidak reversible via rollback sederhana).

### 5.2 Enforcement 1 OPD = 1 akun

- Tambah unique index partial di migration: `users` unique `desa_id` where `role_id` = id role operator (SQLite/MySQL: cukup unique biasa karena tiap OPD memang harus 1 operator; admin `desa_id` selalu null jadi tidak konflik — unique constraint MySQL/SQLite mengizinkan banyak NULL).
- Update `UserIndex::save()` agar pesan error jelas ("Desa ini sudah memiliki 1 akun operator") bukan error database mentah.

---

## 6. Yang TIDAK Berubah (agar scope jelas)

- Struktur internal modul Manajemen Risiko (`mr_sasaran_upr`, `mr_struktur_pelaksana`, `mr_risiko`, `mr_risiko_perlakuan`, `mr_risiko_residual`, `mr_kolom_tambahan`, `mr_layanan_digital`, `mr_pemantauan_risiko`) — **tidak diubah kolomnya**, hanya induknya (`mr_konteks`) yang pindah dari `desa+tahun` ke `layanan`.
- `RiskMatrixCalculator`, `MrRisikoObserver`, `MrKolomTambahanObserver` — tidak berubah logika.
- Role & middleware `RoleMiddleware`, `EnsureKonteksEditable` — tetap dipakai, hanya `EnsureKonteksEditable` perlu dicek ulang karena konsep "editable" kini juga bergantung status Layanan induk, bukan cuma status Konteks itu sendiri.
- Tampilan 5 kartu modul di `dashboard.blade.php` — desain visual dipertahankan, hanya ditambah parameter `layanan_id` dan progres per-layanan.

---

## 7. Urutan Pengerjaan yang Disarankan (Roadmap)

**Fase 1 — Fondasi data Layanan (blocking untuk semua fase lain)**
1. Migration `layanan` + model `Layanan` + relasi `Desa::hasMany(Layanan)`.
2. Form `LayananForm` (create/edit) dengan UX bersection (bukan 1 tabel panjang).
3. Middleware `EnsureHasLayanan`.
4. Migration data lama → placeholder Layanan (§5.1), backup dulu.

**Fase 2 — Homepage & Dashboard per-Layanan**
5. `LayananIndex` (homepage baru, prioritas di atas).
6. Refactor `Dashboard.php` menerima `{layanan}`, badge/progress per layanan.
7. Update `resources/views/components/layouts/*` breadcrumb agar konsisten menampilkan nama Layanan aktif.

**Fase 3 — Rewire Modul Manajemen Risiko ke Layanan**
8. Migration `mr_konteks` tambah `layanan_id`, ubah unique constraint.
9. Update `KonteksIndex`/route agar Konteks MR dibuat otomatis saat Layanan membuka modul MR (bukan tombol manual "Buat Konteks Baru" berdasar tahun).
10. Regression test alur MR penuh (pakai test existing `ManajemenRisikoWorkflowTest` sebagai basis, sesuaikan setup datanya).

**Fase 4 — Penyederhanaan Approval**
11. `SubmitLayanan` (gantikan `SubmitKonteks` untuk level layanan) — validasi kelengkapan 5 modul (untuk saat ini, sebelum modul 2–5 ada, cukup validasi modul MR + tampilkan pesan "modul lain belum tersedia").
12. Hapus UI catatan penolakan dari `ReviewDetail`/`review-detail.blade.php` (konfirmasi dulu ke mitra: reject dihapus total atau cuma catatannya).
13. `ReviewIndex` daftar berbasis Layanan submitted.

**Fase 5 — Kebijakan Akun**
14. Unique `users.desa_id` untuk role operator + validasi pesan error di `UserIndex`.

**Fase 6 — Modul 2–5 (di luar scope perubahan alur ini, tapi bergantung Fase 1)**
15. Setelah struktur `Layanan` stabil, mulai analisis skema tiap modul (Pengetahuan, Perubahan, Keberlangsungan, Relasi) dari 4 file Excel yang sudah tersedia — masing-masing sebagai proyek terpisah mengikuti pola `mr_*`.

**Backlog (tidak diprioritaskan sekarang)**
16. Captcha login.

---

## 8. Pertanyaan Terbuka ke Mitra (perlu dikonfirmasi sebelum implementasi Fase 4)

1. Saat admin me-review Layanan yang sudah lengkap 5 modul: apakah opsi hanya **Approve** (tanpa reject sama sekali), atau tetap ada **reject** tapi benar-benar tanpa kolom catatan/alasan?
2. "Layanan prioritas" di homepage — penentuan prioritas manual oleh operator (toggle saat isi Deskripsi Layanan), atau dihitung otomatis dari suatu kriteria (mis. status "berjalan" + target pengguna publik)? Sejauh ini diasumsikan **manual toggle** (`is_prioritas`).
3. Apakah 1 Layanan wajib melalui ke-5 modul secara berurutan, atau bebas urutan tapi submit baru bisa jika kelimanya lengkap? Diasumsikan **bebas urutan, submit menunggu kelima modul lengkap** (sesuai pola existing `validateCompleteness()` di `SubmitKonteks`).
4. Apakah `tahun_penilaian` pada `mr_konteks` masih relevan dipertahankan sebagai metadata (mis. untuk pelaporan tahunan), meskipun tidak lagi jadi bagian dari unique key?

---

## 9. Checklist Definition of Done (Fase 1–5)

- [ ] Tabel `layanan` dibuat, form pengisian dengan UX bersection sudah berjalan
- [ ] User baru wajib mengisi minimal 1 Layanan sebelum bisa akses halaman lain (guard aktif)
- [ ] Homepage baru menampilkan daftar Layanan, layanan prioritas tampil di posisi atas
- [ ] Dashboard 5-modul bisa dibuka per Layanan terpilih, dengan progres masing-masing modul
- [ ] `mr_konteks` sudah terhubung ke `layanan_id`, data lama termigrasi ke Layanan placeholder tanpa hilang
- [ ] Operator bisa membuat lebih dari 1 Layanan dalam 1 akun
- [ ] Submit ke admin mengecek kelengkapan seluruh modul yang tersedia untuk 1 Layanan tsb
- [ ] UI catatan revisi/penolakan sudah dihapus dari alur review (sesuai konfirmasi §8.1)
- [ ] 1 OPD tidak bisa lagi memiliki lebih dari 1 akun operator (validasi + pesan error jelas)
- [ ] Tidak ada field/asumsi deadline yang ditambahkan ke skema atau UI
- [ ] Style visual (dark slate + emerald) tetap konsisten di semua halaman baru
- [ ] Test existing (`ManajemenRisikoWorkflowTest`, `SasaranFormTest`) disesuaikan dan tetap lulus
