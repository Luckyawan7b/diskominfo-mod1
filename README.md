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

## 🗄️ Struktur Migrasi Database (17 File Bersih)

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
└── 2025_01_01_000013_create_mr_lampiran_table.php       # Bukti dukung polimorfik
```

Skema DDL Oracle/SQL resmi yang identik juga tersimpan di [Schema ERD Data modeler.sql](file:///c:/laragon/www/diskominfo-mod1/Schema%20ERD%20Data%20modeler.sql).

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
