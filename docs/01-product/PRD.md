---
id: PRD-001
title: Product Requirements Document 12erp
document_type: product-requirements-document
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [DOC-PROJECT-BRIEF, SCOPE-001, REQ-CATALOG-001, TRACE-001]
---

# Product Requirements Document 12erp

## Ringkasan

`12erp` adalah aplikasi web operasional internal untuk HR, pengelolaan dokumen, dan administrasi sistem. Produk mengutamakan integritas data tenaga kerja, lifecycle yang dapat ditelusuri, dokumen privat yang terkendali, serta kemampuan administrasi yang diperlukan untuk menjalankan aplikasi.

## Tujuan Produk

1. Menjadi sumber informasi operasional tenaga kerja yang konsisten.
2. Menjalankan lifecycle pegawai melalui state dan tindakan yang eksplisit.
3. Menjaga dokumen privat/perusahaan beserta metadata, versi, dan aksesnya.
4. Mendukung administrasi, audit, konfigurasi, serta operasi aplikasi internal.
5. Menyediakan laporan dan discovery read-only untuk kebutuhan operasional.

## Bukan Tujuan

- Menyediakan seluruh domain yang lazim ada pada produk ERP.
- Menjadikan jumlah module atau bentuk folder sebagai indikator keberhasilan produk.
- Menjanjikan jadwal, fase, skala, availability, atau performa tanpa baseline.
- Mengesahkan setiap perilaku kode sebagai requirement bisnis.
- Menetapkan authorization rinci di luar work item keamanan.

## Model Kapabilitas

### Core

| ID | Kapabilitas | Nilai bisnis | Bukti perilaku saat ini |
|---|---|---|---|
| `CAP-HR-001` | Data induk pegawai | sumber informasi tenaga kerja | CRUD, archive/restore, dan relasi referensi pada area Employees |
| `CAP-HR-002` | Struktur dan referensi tenaga kerja | konsistensi organisasi, posisi, lokasi, status, tipe, level, dan referensi HR | surface pengelolaan master/reference pada area HR |
| `CAP-HR-003` | Lifecycle kontrak kerja | riwayat hubungan kerja effective-dated dan dapat ditelusuri | create, activate, terminate, cancel, supersede, archive/restore |
| `CAP-HR-004` | Onboarding pegawai | checklist masuk yang terkendali | template, instance, task, activate, complete, cancel, archive/restore |
| `CAP-HR-005` | Perubahan penugasan pegawai | perubahan assignment dengan before/after history | create, approve, apply, cancel, archive/restore |
| `CAP-HR-006` | Offboarding pegawai | checklist keluar dan finalisasi yang terkendali | template, instance, task, activate, mark-ready, finalize, cancel, archive/restore |
| `CAP-DOC-001` | Dokumen pegawai dan kepatuhan | metadata, verifikasi, attachment, dan status kedaluwarsa dokumen pegawai | create, verify/reject/resubmit, attachment, delivery, expiry state, archive/restore |
| `CAP-DOC-002` | Lifecycle dokumen privat/perusahaan | ingestion, versioning, archive/restore, dan delivery terkontrol | endpoint document ingestion, version, lifecycle, dan token delivery |

### Supporting

| ID | Kapabilitas | Peran pendukung | Bukti perilaku saat ini |
|---|---|---|---|
| `CAP-ADM-001` | User dan kontrol akses | mengelola identitas administratif, role, dan permission | user lifecycle, role assignment, impersonation, role/permission management |
| `CAP-AUD-001` | Audit dan aktivitas keamanan | menelusuri aksi penting serta aktivitas login | audit log dan login activity |
| `CAP-CFG-001` | Konfigurasi dan template sistem | mengelola konfigurasi aplikasi dan template notifikasi | system settings dan notification templates |
| `CAP-OPS-001` | Operasi dan pemulihan sistem | mendukung backup/restore, queue, dan scheduler | backup/restore, failed job operation, scheduler monitor/run |
| `CAP-EXP-001` | Discovery dan aktivitas pengguna | membantu menemukan data serta aktivitas yang diizinkan | global search dan activity center |
| `CAP-RPT-001` | Laporan HR read-only | menyajikan headcount, status, serta masa berlaku kontrak/dokumen | UI/command laporan tanpa mutation bisnis |

### Deferred

| ID | Kandidat kapabilitas | Alasan belum aktif |
|---|---|---|
| `CAP-NOT-001` | Notifikasi bisnis otomatis | template dan activity surface ada, tetapi pengiriman otomatis untuk expiry/lifecycle belum menjadi perilaku dan requirement terverifikasi |
| `CAP-SELF-001` | Employee self-service | pengguna, use case, data exposure, dan authorization belum disetujui |
| `CAP-INT-001` | Integrasi sistem eksternal | target sistem, kontrak, data, failure mode, dan ownership belum ditetapkan |
| `CAP-I18N-001` | Multi-language | kebutuhan pengguna dan acceptance belum ditetapkan |
| `CAP-RT-001` | Notifikasi real-time | kebutuhan channel, delivery guarantee, dan biaya operasional belum ditetapkan |

Daftar module aktual dan target arsitektur hanya tersedia pada `docs/03-architecture/MODULE-CATALOG.md`. Perubahan nama, jumlah, atau lokasi module tidak mengubah capability ID di atas.

## Alur Pengguna Tingkat Produk

### Mengelola data dan lifecycle pegawai

1. Pengelola yang diotorisasi membuat atau memperbarui data pegawai.
2. Data referensi dan struktur organisasi digunakan sesuai aturan kapabilitas.
3. Kontrak, onboarding, movement, atau offboarding dibuat sebagai lifecycle terpisah.
4. Aksi hanya dapat dilakukan bila state, input, dan authorization mengizinkan.
5. Hasil penting dapat ditelusuri melalui state, riwayat, atau audit yang relevan.

### Mengelola dokumen privat

1. Pengelola yang diotorisasi mencatat atau mengingest dokumen.
2. Sistem menjaga metadata, version/lifecycle, dan ownership.
3. Akses/delivery diberikan melalui mekanisme yang terkontrol.
4. Dokumen pegawai dapat dinilai status verifikasi dan kedaluwarsanya.

### Menjalankan administrasi sistem

1. Administrator mengelola user, role/permission, konfigurasi, dan template.
2. Operator meninjau audit, aktivitas login, queue, scheduler, serta backup/restore sesuai authorization.
3. Search, activity center, dan laporan hanya menyajikan data yang boleh dibaca pengguna.

## Prinsip Penerimaan Produk

| ID | Prinsip |
|---|---|
| `PA-001` | Capability aktif harus mempunyai requirement serta acceptance yang dapat ditelusuri. |
| `PA-002` | Perubahan perilaku tidak boleh diselipkan ke dalam restrukturisasi atau rekonsiliasi dokumentasi. |
| `PA-003` | Aksi sensitif harus melalui authentication, authorization, validation, serta audit sesuai risiko. |
| `PA-004` | Akses dokumen privat tidak boleh melewati ownership dan kontrol delivery yang berlaku. |
| `PA-005` | Laporan/discovery tidak boleh mengambil alih mutation atau aturan bisnis pemilik data. |
| `PA-006` | Target kualitas numerik memerlukan baseline, metode ukur, dan approval tersendiri. |

## Dependensi Keputusan

- ADR-0001 mengatur struktur DDD-Lite target.
- ADR-0002 mengatur ownership kontrak lintas modul.
- `REQUIREMENTS.md` mengatur requirement rinci.
- `SCOPE.md` mengatur status scope capability.
- Authorization, operasi production, serta target kualitas rinci menunggu work item masing-masing.

## Prioritas dan Rilis

Dokumen ini tidak menetapkan fase atau jadwal. Pemilihan pekerjaan dilakukan per work item menggunakan nilai bisnis, dependensi, risiko, dan readiness. Status `core` menunjukkan nilai produk, bukan urutan coding otomatis.

## Persetujuan

```yaml
gate: product-baseline-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - capability menjadi unit scope stabil
  - perilaku kode tetap dibedakan dari keputusan requirement
evidence:
  - SPEC-FTR-PROD-001
```
