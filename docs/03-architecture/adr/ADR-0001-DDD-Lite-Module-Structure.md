---
id: ADR-0001
title: Struktur Modul DDD-Lite yang Baku dan Adaptif
status: accepted
created: 2026-08-12
updated: 2026-08-14
deciders: [Pemilik proyek]
related: [ARC-DDD-LITE-001, REF-HR-WLOC-001]
---

# ADR-0001: Struktur Modul DDD-Lite yang Baku dan Adaptif

## Konteks

Kode saat ini memakai modul Laravel dengan folder yang sebagian besar masih datar, sedangkan dokumen aktif sebelumnya memuat beberapa struktur target yang saling bertentangan. Perbedaan terutama terdapat pada lokasi model, controller, route, DTO, serta kewajiban membuat semua layer sejak awal.

Keputusan manusia pada 2026-08-13 menetapkan bahwa DDD-Lite adalah target utama sekarang, bukan perubahan hipotetis untuk masa depan. Perilaku aplikasi yang ada harus dipetakan dan dipertahankan sebelum pemindahan file atau namespace dimulai.

## Pendorong Keputusan

1. Satu lokasi baku dibutuhkan agar kode mudah ditemukan.
2. Modul sederhana tidak boleh dipaksa memiliki folder atau abstraksi kosong.
3. Modul dibentuk berdasarkan tanggung jawab bisnis, bukan jenis teknologi.
4. Restrukturisasi harus mempertahankan perilaku HTTP, use case, data, permission, event, dan kontrak yang sudah berjalan.
5. Perubahan identifier database mempunyai risiko dan lifecycle berbeda dari pemindahan struktur kode.

## Opsi yang Dipertimbangkan

### Opsi A: Delapan layer wajib untuk semua modul

- Manfaat: bentuk direktori seragam secara visual.
- Biaya/risiko: folder kosong, abstraksi spekulatif, dan beban migrasi tanpa nilai bisnis.

### Opsi B: Lokasi baku dengan struktur minimal sesuai kebutuhan

- Manfaat: konsisten tanpa memaksakan kompleksitas; sesuai prinsip DDD-Lite.
- Biaya/risiko: reviewer harus menilai kebutuhan setiap folder, bukan hanya memeriksa bentuk pohon direktori.

### Opsi C: Mempertahankan struktur datar

- Manfaat: tidak membutuhkan pemindahan file.
- Biaya/risiko: concern teknis dan bisnis tetap bercampur serta tidak memenuhi target proyek.

## Keputusan

Opsi B diterima dengan ketentuan berikut.

1. Lokasi yang dipakai ketika concern-nya ada:

   ```text
   app/Modules/{Boundary}/{Module}/
   |-- Application/
   |   |-- Actions/
   |   |-- DTOs/
   |   |-- Queries/
   |   `-- Services/
   |-- Domain/
   |   |-- Entities/
   |   |-- ValueObjects/
   |   |-- Events/
   |   |-- Services/
   |   `-- Exceptions/
   |-- Infrastructure/
   |   |-- Models/
   |   |-- Repositories/
   |   |-- Providers/
   |   `-- External/
   |-- Presentation/
   |   |-- Http/
   |   |   |-- Controllers/
   |   |   |-- Requests/
   |   |   `-- Resources/
   |   |-- Policies/
   |   `-- Routes/
   |-- Integration/
   |   |-- Contracts/
   |   |-- DTOs/
   |   |-- Events/
   |   |-- Adapters/
   |   `-- Listeners/
   |-- Database/
   |-- Tests/
   |-- module.php
   |-- permissions.php
   `-- navigation.php
   ```

2. Hanya folder dan file yang mempunyai isi serta alasan nyata yang dibuat. `Domain/` tidak diwajibkan untuk CRUD sederhana. `Integration/` hanya ada saat modul memiliki atau menggunakan kontrak/event lintas modul. `permissions.php` dan `navigation.php` juga hanya ada bila modul benar-benar mengekspornya.
3. Eloquent model berada di `Infrastructure/Models/`; tidak diwajibkan membuat domain entity atau repository pembungkus Eloquent.
4. HTTP berada di `Presentation/Http/`; route milik modul berada di `Presentation/Routes/`.
5. Policy authorization yang mengadaptasi Laravel/Spatie, model autentikasi, dan operasi controller berada di `Presentation/Policies/`. Aturan domain murni tidak boleh diletakkan di policy framework tersebut.
6. Kontrak lintas modul dimiliki modul bisnis penyedia dan diekspos melalui `Integration/Contracts/` beserta DTO/event versioned yang relevan.
7. Test yang secara jelas dimiliki modul ditempatkan di `Tests/`; test arsitektur dan lintas sistem boleh tetap berada pada test suite tingkat aplikasi.
8. Migrasi dilakukan per vertical slice terkecil yang dapat diverifikasi. Struktur campuran sementara diperbolehkan dan harus dicatat.
9. Perubahan primary key ke ULID tidak termasuk ADR atau work item struktur ini. Perubahan tersebut dipisahkan ke `MIG-ID-001` dan tetap ditunda sampai memiliki pemetaan data, rollback, serta persetujuan tersendiri.

## Konsekuensi

### Positif

- Semua concern mempunyai lokasi target yang tunggal.
- Struktur tetap ringan untuk modul sederhana.
- Kontrak lintas modul kembali ke pemilik bisnisnya.
- Risiko pemindahan kode tidak tercampur dengan risiko migrasi data.

### Negatif

- Repository berada dalam keadaan struktur campuran selama migrasi bertahap.
- Setiap pemindahan namespace harus memperbarui consumer, binding, route loading, dan test.

### Netral / Tindak Lanjut

- Evaluasi katalog modul menentukan modul yang dipertahankan, diganti nama, atau dihentikan.
- Generator modul harus menghasilkan struktur minimal berdasarkan opsi, bukan seluruh folder kosong.
- Baseline aktif diperbarui hanya berdasarkan bukti kode atau keputusan yang telah diterima.

## Validasi

Setiap irisan migrasi wajib membuktikan:

1. tidak ada perubahan perilaku route, response, permission, data, event, atau kontrak;
2. autoload dan registrasi provider berhasil;
3. test terfokus dan pemeriksaan arsitektur lulus;
4. referensi namespace lama tidak tersisa pada scope irisan;
5. dokumentasi dan manifest bukti tersinkron.

## Penggantian

ADR ini menggantikan semua variasi struktur target dalam dokumen aktif yang mewajibkan delapan layer penuh, menggunakan `Infrastructure/Persistence/Models`, `Presentation/Controllers`, root `Routes/`, atau `Application/DTO` sebagai lokasi baku.

Keputusan ini tidak menghapus bukti historis. Dokumen sebelum SEOS tetap dapat ditelusuri melalui snapshot `BL-2026-001-pre-seos`.

## Catatan Persetujuan

```yaml
gate: architecture-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - perilaku aplikasi dipertahankan
  - struktur bersifat minimal sesuai kebutuhan
  - migrasi ULID dipisahkan
evidence:
  - konfirmasi eksplisit melalui percakapan evaluasi dokumentasi
  - konfirmasi penempatan policy framework pada Presentation melalui interview pilot WorkLocations, 2026-08-14
```
