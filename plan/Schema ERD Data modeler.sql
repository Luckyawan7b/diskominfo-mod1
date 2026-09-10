-- =========================================================================
-- 1. master data & authentication
-- =========================================================================

create table roles (
    id number generated always as identity,
    nama varchar2(191 char) not null,
    label varchar2(191 char) not null,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    constraint pk_roles primary key (id),
    constraint uq_roles_name unique (nama)
);

create table users (
    id number generated always as identity,
    role_id number not null,
    nama_dinas varchar2(191 char),
    alias varchar2(191 char),
    nama varchar2(191 char) not null,
    email varchar2(191 char) not null,
    email_verified_at timestamp,
    password varchar2(191 char) not null,
    remember_token varchar2(100 char),
    created_at timestamp default systimestamp,
    updated_at timestamp,
    deleted_at timestamp,
    constraint pk_users primary key (id),
    constraint uq_users_email unique (email),
    constraint fk_users_role foreign key (role_id) references roles(id) on delete cascade
);

-- =========================================================================
-- 2. manajemen layanan & referensi
-- =========================================================================

create table layanans (
    id number generated always as identity,
    unit_pelaksana varchar2(191 char), 
    bidang_bagian varchar2(191 char),
    status_layanan varchar2(50 char) default 'berjalan' not null,
    nama_layanan varchar2(191 char) not null,
    deskripsi_layanan varchar2(4000 char),
    target_pengguna varchar2(100 char),
    kl_terkait varchar2(191 char),
    supplier_data varchar2(191 char),
    nama_data_input varchar2(4000 char),
    nama_data_output varchar2(4000 char),
    sifat_data varchar2(50 char),
    jenis_data varchar2(191 char),
    validitas_data varchar2(191 char),
    interoperabilitas number(1) default 0 not null,
    tujuan_integrasi varchar2(4000 char),
    metode_integrasi varchar2(191 char),
    link_dokumen_integrasi varchar2(191 char),
    nama_aplikasi varchar2(191 char),
    tipe_aplikasi varchar2(191 char),
    link_aplikasi varchar2(191 char),
    keluaran_aplikasi varchar2(4000 char),
    letak_server varchar2(191 char),
    link_dpa varchar2(191 char),
    tahun_pembuatan number(4,0),
    link_sla varchar2(191 char),
    link_sop varchar2(191 char),
    helpdesk varchar2(191 char),
    is_prioritas number(1) default 0 not null,
    created_by number not null,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    deleted_at timestamp,
    constraint pk_layanans primary key (id),
    constraint fk_layanans_user foreign key (created_by) references users(id) on delete cascade,
    constraint chk_status_layanan check (status_layanan in ('berjalan', 'direncanakan', 'dihentikan')),
    constraint chk_target_pengguna check (target_pengguna in ('publik/masyarakat', 'internal pemerintahan')),
    constraint chk_sifat_data check (sifat_data in ('terbuka', 'terbatas', 'tertutup'))
);

create table ref_sasaran_nasional (
    id number generated always as identity,
    teks_sasaran varchar2(500 char) not null,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    constraint pk_ref_sasaran primary key (id),
    constraint uq_sasaran_teks unique (teks_sasaran)
);

-- =========================================================================
-- 3. modul transaksi manajemen risiko (mr) spbe
-- =========================================================================

create table mr_konteks (
    id number generated always as identity,
    layanan_id number not null,
    nama_instansi varchar2(191 char) not null,
    nama_upr varchar2(191 char) not null,
    tugas_upr varchar2(4000 char),
    fungsi_upr varchar2(4000 char),
    tahun_penilaian number(4,0) not null,
    tahun_pelaksanaan number(4,0),
    selera_risiko number(3,0) default 16 not null,
    created_by number not null,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    deleted_at timestamp,
    constraint pk_mr_konteks primary key (id),
    constraint uq_konteks_layanan unique (layanan_id),
    constraint fk_konteks_layanan foreign key (layanan_id) references layanans(id) on delete cascade,
    constraint fk_konteks_user foreign key (created_by) references users(id) on delete cascade
);

create table mr_sasaran_upr (
    id number generated always as identity,
    mr_konteks_id number not null,
    ref_sasaran_nasional_id number,
    sasaran_upr varchar2(4000 char),
    urutan number(5,0) default 0 not null,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    deleted_at timestamp,
    constraint pk_mr_sasaran_upr primary key (id),
    constraint fk_sasaran_upr_konteks foreign key (mr_konteks_id) references mr_konteks(id) on delete cascade,
    constraint fk_sasaran_upr_ref foreign key (ref_sasaran_nasional_id) references ref_sasaran_nasional(id) on delete set null
);

create table mr_struktur_pelaksana (
    id number generated always as identity,
    mr_konteks_id number not null,
    pemilik_risiko varchar2(191 char),
    koordinator_risiko varchar2(191 char),
    pengelola_risiko varchar2(4000 char),
    created_at timestamp default systimestamp,
    updated_at timestamp,
    constraint pk_struktur_pelaksana primary key (id),
    constraint uq_struktur_konteks unique (mr_konteks_id),
    constraint fk_struktur_konteks foreign key (mr_konteks_id) references mr_konteks(id) on delete cascade
);

create table mr_indikator_kinerja (
    id number generated always as identity,
    mr_sasaran_upr_id number not null,
    indikator_kinerja varchar2(191 char),
    target_kinerja varchar2(191 char),
    urutan number(5,0) default 0 not null,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    deleted_at timestamp,
    constraint pk_indikator_kinerja primary key (id),
    constraint fk_indikator_sasaran foreign key (mr_sasaran_upr_id) references mr_sasaran_upr(id) on delete cascade
);

create table mr_risiko (
    id number generated always as identity,
    mr_konteks_id number not null,
    mr_sasaran_upr_id number,
    kategori_risiko varchar2(191 char),
    sasaran_pembangunan_nasional_snapshot varchar2(4000 char),
    sasaran_upr_snapshot varchar2(4000 char),
    indikator_kinerja_snapshot varchar2(191 char),
    kode_risiko varchar2(191 char) not null,
    peristiwa_risiko varchar2(4000 char) not null,
    penyebab varchar2(4000 char),
    dampak varchar2(4000 char),
    area_dampak varchar2(100 char),
    level_kemungkinan number(2,0),
    level_dampak number(2,0),
    besaran_risiko number(3,0),
    prioritas_risiko number(5,0),
    created_by number,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    deleted_at timestamp,
    constraint pk_mr_risiko primary key (id),
    constraint uq_risiko_kode unique (mr_konteks_id, kode_risiko),
    constraint fk_risiko_konteks foreign key (mr_konteks_id) references mr_konteks(id) on delete cascade,
    constraint fk_risiko_sasaran foreign key (mr_sasaran_upr_id) references mr_sasaran_upr(id) on delete set null,
    constraint fk_risiko_user foreign key (created_by) references users(id) on delete set null,
    constraint chk_area_dampak check (area_dampak in ('penurunan reputasi', 'keuangan', 'gangguan terhadap layanan organisasi', 'penurunan kinerja'))
);

create table mr_risiko_perlakuan (
    id number generated always as identity,
    mr_risiko_id number not null,
    keputusan_perlakuan varchar2(50 char),
    deskripsi_detail_perlakuan varchar2(4000 char),
    waktu_rencana_perlakuan varchar2(191 char),
    penanggung_jawab varchar2(191 char),
    created_at timestamp default systimestamp,
    updated_at timestamp,
    constraint pk_mr_risiko_perlakuan primary key (id),
    constraint uq_perlakuan_risiko unique (mr_risiko_id),
    constraint fk_perlakuan_risiko foreign key (mr_risiko_id) references mr_risiko(id) on delete cascade,
    constraint chk_keputusan_perlakuan check (keputusan_perlakuan in ('menerima risiko', 'mengurangi risiko', 'membagi risiko', 'menghindari risiko'))
);

create table mr_risiko_residual (
    id number generated always as identity,
    mr_risiko_id number not null,
    level_kemungkinan number(2,0),
    level_dampak number(2,0),
    besaran_risiko number(3,0),
    keterangan_residual varchar2(4000 char),
    created_at timestamp default systimestamp,
    updated_at timestamp,
    constraint pk_mr_risiko_residual primary key (id),
    constraint uq_residual_risiko unique (mr_risiko_id),
    constraint fk_residual_risiko foreign key (mr_risiko_id) references mr_risiko(id) on delete cascade
);

create table mr_kolom_tambahan (
    id number generated always as identity,
    mr_risiko_id number not null,
    layanan_pendukung varchar2(191 char),
    layanan_prioritas varchar2(50 char),
    pemilik_layanan varchar2(50 char),
    strategis_atau_operasional varchar2(50 char),
    lintas_sektor number(1) default 0 not null,
    ippd_terkait varchar2(191 char),
    membutuhkan_perubahan number(1) default 0 not null,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    constraint pk_mr_kolom_tambahan primary key (id),
    constraint uq_kolom_tambahan_risiko unique (mr_risiko_id),
    constraint fk_kolom_tambahan_risiko foreign key (mr_risiko_id) references mr_risiko(id) on delete cascade,
    constraint chk_layanan_prioritas check (layanan_prioritas in ('prioritas', 'tematik', 'instansional')),
    constraint chk_pemilik_layanan check (pemilik_layanan in ('pusat', 'instansi lain', 'milik sendiri')),
    constraint chk_strategis_operasional check (strategis_atau_operasional in ('strategis', 'operasional'))
);

create table mr_layanan_digital (
    id number generated always as identity,
    mr_risiko_id number not null,
    perlu_mkb number(1),
    pic varchar2(191 char),
    target_waktu_penyusunan varchar2(191 char),
    created_at timestamp default systimestamp,
    updated_at timestamp,
    constraint pk_mr_layanan_digital primary key (id),
    constraint fk_layanan_digital_risiko foreign key (mr_risiko_id) references mr_risiko(id) on delete cascade
);

create table mr_pemantauan_risiko (
    id number generated always as identity,
    mr_risiko_id number not null,
    periode varchar2(50 char) not null,
    tahun number(4,0) not null,
    hasil_pelaksanaan varchar2(4000 char),
    data_dukung_catatan varchar2(4000 char),
    created_by number,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    deleted_at timestamp,
    constraint pk_mr_pemantauan_risiko primary key (id),
    constraint uq_pemantauan unique (mr_risiko_id, periode, tahun),
    constraint fk_pemantauan_risiko foreign key (mr_risiko_id) references mr_risiko(id) on delete cascade,
    constraint fk_pemantauan_user foreign key (created_by) references users(id) on delete set null,
    constraint chk_periode check (periode in ('semester_1', 'semester_2'))
);

create table mr_lampiran (
    id number generated always as identity,
    lampirable_type varchar2(191 char) not null,
    lampirable_id number not null,
    nama_file varchar2(191 char) not null,
    path_file varchar2(191 char) not null,
    mime_type varchar2(191 char),
    ukuran_kb number(10,0),
    uploaded_by number,
    created_at timestamp default systimestamp,
    updated_at timestamp,
    deleted_at timestamp,
    constraint pk_mr_lampiran primary key (id),
    constraint fk_lampiran_user foreign key (uploaded_by) references users(id) on delete set null
);
