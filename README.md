# Sistem Informasi Manajemen Risiko & Layanan SPBE - Diskominfo

Aplikasi ini merupakan modul manajemen risiko dan katalog layanan Sistem Pemerintahan Berbasis Elektronik (SPBE) untuk **Pemerintah Daerah / Dinas (Perangkat Daerah)**, dikelola oleh Dinas Komunikasi dan Informatika (Diskominfo).

---

## 📌 Konteks Penting untuk Agen & Pengembang Selanjutnya

Dokumen ini memuat catatan arsitektur dan riwayat perubahan penting agar agen AI maupun pengembang berikutnya memahami konteks sistem saat ini dan tidak mengembalikan kode ke pola lama.

---

### 1. Peralihan dari Level "Desa" ke Level "Dinas / Perangkat Daerah"
- **Latar Belakang**: Prototipe awal aplikasi menggunakan entitas `desa` (`desa_id`). Saat ini aplikasi resmi ditujukan untuk tingkat **Dinas / OPD (Organisasi Perangkat Daerah)**.
- **Perubahan Database**:
  - Tabel `desa` dan semua kolom `desa_id` telah **dihapus sepenuhnya** dari database migrasi.
  - Tabel `users` kini memiliki kolom `nama_dinas` (varchar 191) dan `alias` (varchar 191, contoh: `Diskominfo`, `Bappeda`, `Dinkes`).
  - Tabel `layanans` kini memiliki kolom `unit_pelaksana` (varchar 191).
  - Tabel `mr_konteks` terhubung langsung ke `layanans` via `layanan_id` (`UNIQUE FK`) dan memiliki `nama_instansi` serta `nama_upr`.

---

### 2. Penghapusan Alur Approval & Penolakan (Sistem CRUD Biasa)
- **Latar Belakang**: Sebelumnya sistem dirancang dengan alur persetujuan bertingkat (`draft` → `submitted` → `approved` / `rejected` → `archived`). Berdasarkan keputusan bisnis terbaru:
  - Perangkat Daerah mengirimkan data yang **bersifat langsung final**, namun **tetap selalu bisa diubah kapan saja (CRUD biasa)**.
  - Admin **tidak melakukan revisi atau penolakan di dalam sistem** (proses evaluasi/revisi dilakukan di luar sistem/offline).
- **Perubahan Database**:
  - Kolom `status` ('draft', 'submitted', 'approved', 'rejected', 'archived') pada `mr_konteks` **telah dihapus**.
  - Kolom `status` ('draft', 'submitted', 'approved', 'rejected') pada `mr_risiko` **telah dihapus**.
  - Kolom `catatan_penolakan` pada `mr_risiko` **telah dihapus**.
- **Perubahan Model**:
  - `MrKonteks::isEditableByOperator()` selalu mengembalikan `true`.
  - `MrRisiko::isEditableByOperator()` selalu mengembalikan `true`.

---

### 3. Perubahan Kategori Risiko (Dropdown Referensi → Input Teks)
- **Latar Belakang**: Kategori risiko tidak lagi dibatasi oleh 10 kategori baku yang terdaftar di tabel referensi terpisah, melainkan dapat diisi langsung oleh user.
- **Perubahan Database**:
  - Tabel `ref_kategori_risiko` dan seedernya telah **dihapus sepenuhnya**.
  - Kolom `ref_kategori_risiko_id` pada tabel `mr_risiko` digantikan oleh kolom teks biasa:
    ```php
    $table->string('kategori_risiko')->nullable();
    ```
- **Catatan UI**: Komponen form input risiko harus menggunakan input teks (`<input type="text">`), bukan `<select>` dropdown ke tabel master.

---

### 4. Perhitungan Otomatis Kolom `prioritas_risiko`
- Kolom `prioritas_risiko` (unsigned smallint) pada tabel `mr_risiko` **bukan diinput manual oleh user**.
- Nilai dihitung dan diurutkan otomatis oleh `App\Observers\MrRisikoObserver` berdasarkan ranking besaran kritis risiko (`besaran_risiko DESC`) dalam satu konteks penilaian yang sama:
  - `besaran_risiko` = `level_kemungkinan` × `level_dampak` (dihitung via `RiskMatrixCalculator`).
  - Baris dengan besaran risiko tertinggi otomatis mendapat `prioritas_risiko = 1`.

---

### 5. Alur Navigasi & Pembuatan Konteks Manajemen Risiko (Multi-tahun)
- **Latar Belakang**: Sebelumnya, mengakses Manajemen Risiko dari Dashboard akan mengarahkan pengguna secara otomatis ke form isian, dan membuat satu buah dokumen konteks secara otomatis jika belum ada di database.
- **Perubahan Navigasi (Baru)**:
  - Mengakses Manajemen Risiko dari Dashboard kini **selalu mengarahkan pengguna ke halaman Daftar Konteks Risiko**.
  - Daftar tersebut di-*filter* khusus untuk menampilkan riwayat dokumen risiko milik Layanan yang diklik (menggunakan `session('active_layanan_id')`).
  - Fitur **Buat Konteks Baru** dipindahkan ke dalam bentuk Modal di halaman daftar tersebut, sehingga operator dapat membuat dokumen risiko untuk **tahun penilaian yang berbeda-beda** (multi-tahun) pada layanan yang sama. Data Instansi dan UPR akan diisi otomatis berdasarkan data Layanan terkait.

---

### 6. Implementasi Modul Manajemen Pengetahuan (MPN)
- **Latar Belakang**: Aplikasi telah ditambahkan Modul Manajemen Pengetahuan (MPN) yang berfungsi untuk mengelola siklus pengetahuan SPBE.
- **Struktur Database**:
  - Modul ini menggunakan tabel referensi baru: `ref_aspek_pemdi`, `ref_indikator_pemdi`, dan `ref_metode_pengolahan`.
  - Tabel utama terdiri dari: `mpn_konteks`, `mpn_indikator_capaian`, `mpn_evaluasi_indikator`, `mpn_pengetahuan`, dan `mpn_rencana_dokumentasi`.
  - Tabel transaksi: `mpn_pengumpulan`, `mpn_pemanfaatan`, dan `mpn_alih_pengetahuan`.
- **Fitur Otomatis (Observers)**:
  - `MpnPengetahuanObserver`: Otomatis membuat atau menghapus baris di tabel `mpn_rencana_dokumentasi` jika pengetahuan belum/sudah terdokumentasi (berdasarkan field `apakah_terdokumentasi`).
  - `MpnPemanfaatanObserver`: Secara otomatis menghitung ulang rata-rata (`avg`) dari *rating* pemanfaatan untuk di-update (`updateQuietly`) ke field `rating_pengetahuan` pada tabel `mpn_pengumpulan`.
- **Integrasi**: `MpnKonteks` kini juga mengimplementasikan kontrak `HasLayananContext` sehingga secara konsisten menggunakan pengecekan middleware akses yang sama dengan modul Manajemen Risiko.

---

### 7. Modul Manajemen Perubahan (MPR)
- **Lingkup saat ini**: migrasi database, seeder referensi, model, factory, observer/service/rule pendukung, tab Tempat Sampah, dan ERD. **Routing & komponen Livewire MPR belum dibuat.** Sumber: Excel `24072026_PEMDA_MPR-Manajemen Perubahan Layanan Digital Formulir` (sheet MPR01–MPR05), salinannya di `plan/excel/`. Rencana & status pengerjaan: `plan/MPR_MIGRATION_PLAN.md` dan `plan/MPR_TASKS.md`.
- **Struktur Database** (prefix `mpr_`, referensi `ref_`; 30 migrasi `2025_03_01_000001`–`000030`):
  - Referensi (11): `ref_klasifikasi_perubahan`, `ref_lingkup_perubahan`, `ref_area_dampak_org`, `ref_indikator_area_dampak`, `ref_ukuran_kompleksitas`, `ref_strategi_it`, `ref_strategi_organisasi`, `ref_kegiatan_strategi_organisasi`, `ref_kategori_stakeholder`, `ref_jenis_resistensi`, `ref_tingkat_resistensi`. `ref_aspek_pemdi` & `ref_indikator_pemdi` dipakai ulang dari MPN.
  - Inti: `mpr_konteks` (per layanan, unique `layanan_id`+`tahun_penilaian`), `mpr_analisis_prioritas` (+ pivot `mpr_prioritas_aspek`, `mpr_prioritas_indikator`), `mpr_perubahan` (induk semua formulir), `mpr_analisis_risiko` (2A), `mpr_analisis_organisasi` + `mpr_org_area` + `mpr_org_area_indikator` (2B), `mpr_persetujuan`.
  - Formulir 3–5: `mpr_implementasi`, `mpr_sumber_daya`, `mpr_indikator_keberhasilan`, `mpr_strategi`, `mpr_stakeholder`, `mpr_komunikasi`, `mpr_pelatihan`, `mpr_evaluasi`, `mpr_logbook` (bukti pelaksanaan memakai `mr_lampiran` polimorfik).
- **Soft delete** hanya pada `mpr_konteks`, `mpr_perubahan`, `mpr_analisis_risiko` (lengkap dengan `deleted_by`, tampil di Tempat Sampah). Tabel anak tidak soft delete: soft delete induk **tidak menyentuh anak** (hanya tersembunyi, restore mengembalikan semuanya); anak baru terhapus saat induk di-*force delete* (cascade FK).
- **ID Perubahan**: format `MPR-{ALIAS}-{TAHUN}-{NNN}` (mis. `MPR-DISKOMINFO-2025-001`), dibuat `App\Services\MprIdGenerator` (nomor dicari termasuk data soft-deleted sehingga tidak pernah dipakai ulang; transaksi + retry sekali). Catatan: kode MPN memakai prefix `MRP-`, jangan disamakan.
- **Kode risiko 2A**: `App\Rules\KodeRisikoMprUnik` menolak kode yang sudah ada pada `mr_risiko` atau `mpr_analisis_risiko` layanan yang sama (data soft-deleted diabaikan). Bukan unique DB (unique MySQL tidak mengabaikan soft-deleted), melainkan index biasa `(layanan_id, kode_risiko)`.
- **Skala label risiko berbeda**: MPR memakai **3 level** (`RiskMatrixCalculator::labelMpr()`: 1–5 Rendah, 6–16 Sedang, ≥17 Tinggi), sedangkan MR memakai **4 level** (`label()`: 1–4 / 5–9 / 10–16 / 17–25). Jangan mencampur keduanya.
- **Fitur Otomatis**: `MprAnalisisRisikoObserver` (hitung `besaran_risiko`; `layanan_id` selalu ditimpa dari `perubahan→konteks→layanan_id`); `MprAnalisisPrioritas` mengisi `nama_layanan_prioritas` otomatis dari nama layanan yang sedang diakses (snapshot); `MprKesimpulanOrganisasi` (kesimpulan 2B sesuai formula Excel); `MprAlurResolver` (menentukan formulir wajib dari flag klasifikasi & lingkup, tanpa menyimpan status alur).
- **Persetujuan** hanyalah data isian (`mpr_persetujuan`: status + teks PIC), bukan alur approval dengan akun approver.
- **Data referensi yang belum lengkap** (lihat `// TODO` di seeder): Strategi IT (Tabel 3b, hanya Canary/Rolling/Serverless dan hanya Serverless yang punya uraian) dan Tingkat Resistensi (Tabel 3e, hanya Champion & Floating Voter).

---

## 🗄️ Struktur Migrasi Database (Total 65 File)

Seluruh migrasi tambahan (patch alter table, drop table sementara, placeholder data migrasi) telah dibersihkan dan disusun ulang dari awal (*clean slate*) sesuai urutan dependensi foreign key:

```text
database/migrations/
├── 0001_01_01_000000_create_roles_table.php             # Roles user (admin, operator)
├── 0001_01_01_000001_create_users_table.php             # Users (role_id, nama_dinas, alias, softDeletes) + Auth tokens
├── 0001_01_01_000002_create_cache_table.php             # Driver cache
├── 0001_01_01_000003_create_jobs_table.php              # Driver queues
├── 2025_01_01_000001_create_layanans_table.php          # Manajemen Layanan SPBE (27 atribut + unit_pelaksana)
├── 2025_01_01_000002_create_ref_sasaran_nasional_table.php # Referensi Sasaran Strategis Nasional
├── 2025_01_01_000003_create_mr_konteks_table.php        # Konteks SPBE (layanan_id UNIQUE, tahun_pelaksanaan)
├── 2025_01_01_000004_create_mr_struktur_pelaksana_table.php # Struktur pelaksana UPR (1:1 mr_konteks)
├── 2025_01_01_000005_create_mr_sasaran_upr_table.php    # Sasaran UPR
├── 2025_01_01_000006_create_mr_indikator_kinerja_table.php # Indikator kinerja sasaran UPR
├── 2025_01_01_000007_create_mr_risiko_table.php         # Baris risiko (kategori_risiko string, mr_sasaran_upr_id)
├── 2025_01_01_000008_create_mr_risiko_perlakuan_table.php # Rencana tindak perlakuan risiko
├── 2025_01_01_000009_create_mr_risiko_residual_table.php  # Risiko residual pasca perlakuan
├── 2025_01_01_000010_create_mr_kolom_tambahan_table.php # SPBE Digital (Bagian E)
├── 2025_01_01_000011_create_mr_layanan_digital_table.php # MKB (Manajemen Keberlangsungan Bisnis)
├── 2025_01_01_000012_create_mr_pemantauan_risiko_table.php # Pemantauan berkala Semester 1 & 2
├── 2025_01_01_000013_create_mr_lampiran_table.php       # Bukti dukung polimorfik
├── 2025_01_01_000014_add_unique_constraint_to_mr_konteks_table.php # Unique constraint untuk (layanan_id, tahun_penilaian)
├── 2025_02_01_000001_create_ref_aspek_pemdi_table.php   # [MPN] Referensi Aspek Pemdi
├── 2025_02_01_000002_create_ref_indikator_pemdi_table.php # [MPN] Referensi Indikator Pemdi
├── 2025_02_01_000003_create_ref_metode_pengolahan_table.php # [MPN] Referensi Metode Pengolahan
├── 2025_02_01_000004_create_mpn_konteks_table.php       # [MPN] Konteks MPN
├── 2025_02_01_000005_create_mpn_indikator_capaian_table.php # [MPN] Indikator Capaian MPN
├── 2025_02_01_000006_create_mpn_evaluasi_indikator_table.php # [MPN] Evaluasi Indikator Capaian
├── 2025_02_01_000007_create_mpn_pengetahuan_table.php   # [MPN] Pengetahuan
├── 2025_02_01_000008_create_mpn_rencana_dokumentasi_table.php # [MPN] Rencana Dokumentasi
├── 2025_02_01_000009_create_mpn_pengumpulan_table.php   # [MPN] Pengumpulan Pengetahuan
├── 2025_02_01_000010_create_mpn_pemanfaatan_table.php   # [MPN] Pemanfaatan Pengetahuan
├── 2025_02_01_000011_create_mpn_alih_pengetahuan_table.php # [MPN] Alih Pengetahuan
├── 2025_03_01_000001..000011_create_ref_*_table.php       # [MPR] 11 tabel referensi (klasifikasi, lingkup, area dampak, indikator area dampak, ukuran kompleksitas, strategi IT/organisasi, kegiatan strategi, kategori stakeholder, jenis & tingkat resistensi)
├── 2025_03_01_000012_create_mpr_konteks_table.php       # [MPR] Konteks MPR (soft delete)
├── 2025_03_01_000013..000015_*                          # [MPR] Analisis prioritas + pivot aspek & indikator
├── 2025_03_01_000016_create_mpr_perubahan_table.php     # [MPR] Perubahan (induk semua formulir, soft delete)
├── 2025_03_01_000017_create_mpr_analisis_risiko_table.php # [MPR] Analisis risiko 2A (soft delete)
├── 2025_03_01_000018..000020_*                          # [MPR] Analisis organisasi 2B (header, area, indikator)
├── 2025_03_01_000021_create_mpr_persetujuan_table.php   # [MPR] Persetujuan per tahap
└── 2025_03_01_000022..000030_*                          # [MPR] Formulir 3–5: implementasi, sumber daya, indikator keberhasilan, strategi, stakeholder, komunikasi, pelatihan, evaluasi, logbook
```

Skema DDL Oracle/SQL resmi yang identik (termasuk modul MPR) tersimpan di [Schema ERD Data modeler.sql](plan/Schema%20ERD%20Data%20modeler.sql). Serta [ERD MP.sql](ERD%20MP.sql) untuk struktur MPN spesifik.

---

## 🛠️ Panduan untuk Tugas UI / Komponen Selanjutnya

Jika Anda bertugas memperbaiki atau melanjutkan pengembangan komponen Livewire / Blade:

1. **Bersihkan Kode yang Masih Memanggil Desa**:
   - Komponen lama seperti `App\Livewire\Admin\Desa\DesaIndex` tidak lagi memiliki tabel di database.
   - Filter di `KonteksIndex.php` yang sebelumnya menggunakan `desa_id` / `Desa::all()` harus disesuaikan menjadi filter berdasarkan dinas/OPD (`User::where('role_id', ...)->pluck('nama_dinas')` atau `alias`).
2. **Sederhanakan Alur Pengiriman Form**:
   - Hapus komponen atau tombol "Kirim untuk Review" / "Submit Konteks" yang mengunci form menjadi read-only.
   - Hapus tampilan badge status approval (`Draft`, `Submitted`, `Approved`, `Rejected`) karena status tersebut sudah tidak ada di database.
   - Form cukup memiliki tombol **"Simpan"** / **"Perbarui"** standar CRUD.
3. **Form Input Risiko**:
   - Ganti elemen select `ref_kategori_risiko_id` menjadi text field `kategori_risiko`.

---

## 🚀 Setup & Testing

### Fresh Migration & Seeding
```bash
php artisan migrate:fresh
php artisan db:seed
```

### Akun Bawaan Seeder:
- **Admin**: `admin@diskominfo.test` / password: `password`
- **Operator Diskominfo**: `operator.diskominfo@diskominfo.test` / password: `password`
- **Operator Bappeda**: `operator.bappeda@diskominfo.test` / password: `password`
- **Operator Dinkes**: `operator.dinkes@diskominfo.test` / password: `password`
