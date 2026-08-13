---
id: DOC-PROJECT-BRIEF
title: Ringkasan Produk 12erp
document_type: product-brief
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [PRD-001, SCOPE-001, REQ-CATALOG-001, ADR-0001, ADR-0002]
---

# Ringkasan Produk 12erp

## Identitas Produk

- Nama produk: `12erp`.
- Bentuk produk: aplikasi web operasional internal.
- Fokus: pengelolaan sumber daya manusia, dokumen privat/perusahaan, serta administrasi sistem.
- Konteks arsitektur: Laravel modular monolith dengan frontend React/Inertia dan target struktur DDD-Lite adaptif.

Istilah ERP adalah payung proyek. Istilah tersebut tidak menjadi janji tersirat untuk menyediakan finance, accounting, inventory, procurement, sales, CRM, payroll, atau seluruh domain ERP umum.

## Masalah yang Diselesaikan

Organisasi membutuhkan satu aplikasi internal untuk:

1. menjaga data pegawai, referensi tenaga kerja, dan struktur organisasi tetap konsisten;
2. mengendalikan lifecycle kontrak, onboarding, perubahan penugasan, dan offboarding;
3. mengelola metadata, kepatuhan, versioning, akses, serta delivery dokumen privat;
4. menyediakan administrasi user, kontrol akses, audit, konfigurasi, dan operasi sistem;
5. menyajikan informasi operasional yang dapat ditelusuri tanpa menjadikan laporan sebagai pemilik aturan bisnis.

## Outcome Produk

| ID | Outcome | Indikator keberhasilan tanpa target spekulatif |
|---|---|---|
| `OUT-001` | Integritas informasi tenaga kerja | data pegawai dan referensinya dapat dikelola dengan aturan validasi serta lifecycle yang eksplisit |
| `OUT-002` | Lifecycle pegawai dapat ditelusuri | onboarding, kontrak, movement, dan offboarding memiliki status serta transisi yang dapat diverifikasi |
| `OUT-003` | Dokumen privat terkendali | dokumen memiliki ownership, metadata, versioning/lifecycle, dan akses yang terkontrol |
| `OUT-004` | Administrasi sistem terkendali | user, akses, konfigurasi, audit, dan operasi sistem mempunyai surface pengelolaan yang jelas |
| `OUT-005` | Keputusan berbasis informasi operasional | laporan dan pencarian menyajikan data read-only yang relevan serta dapat ditelusuri ke sumbernya |

## Pengguna Tingkat Tinggi

| Kelompok | Kebutuhan utama | Catatan |
|---|---|---|
| Pengelola HR | mengelola data dan lifecycle tenaga kerja | role serta permission rinci ditetapkan melalui baseline authorization tersendiri |
| Pemberi persetujuan/manager | meninjau dan menjalankan aksi lifecycle yang diotorisasi | alur approval per kapabilitas harus dibuktikan, bukan diasumsikan dari nama persona |
| Pengelola dokumen | mengelola metadata, kepatuhan, penyimpanan, dan akses dokumen | dapat berupa fungsi HR atau fungsi internal lain sesuai keputusan organisasi |
| Administrator/operator sistem | mengelola user, akses, konfigurasi, audit, backup, queue, dan scheduler | aksi sensitif memerlukan review keamanan terpisah |
| Pengguna laporan/manajemen | membaca ringkasan dan laporan operasional | akses data mengikuti authorization yang berlaku |

Employee sebagai pengguna self-service langsung belum menjadi keputusan aktif. Pada baseline ini employee adalah subjek data dan peserta lifecycle; kebutuhan portal self-service tetap `deferred`.

## Nilai Produk

1. Mengurangi duplikasi serta inkonsistensi data tenaga kerja.
2. Membuat perubahan lifecycle eksplisit, dapat diaudit, dan lebih aman dijalankan.
3. Menjaga dokumen privat dekat dengan ownership serta aturan aksesnya.
4. Menyatukan kapabilitas administrasi yang dibutuhkan aplikasi internal.
5. Memungkinkan evolusi incremental melalui boundary bisnis dan DDD-Lite tanpa menjadikan jumlah modul sebagai target produk.

## Prinsip Produk

- Capability bisnis adalah unit scope; module adalah keputusan arsitektur.
- Perilaku kode adalah bukti keadaan saat ini, bukan approval requirement otomatis.
- Perilaku yang sudah ada dipertahankan sebagai baseline kompatibilitas sampai perubahan disetujui melalui work item.
- Kapabilitas baru, perubahan scope, dan target kualitas terukur harus mempunyai acceptance serta approval.
- Urutan implementasi ditentukan per work item berdasarkan nilai bisnis, dependensi, risiko, dan readiness.

## Batasan Aktif

- Digunakan untuk operasi internal satu organisasi; multi-tenant SaaS belum termasuk scope.
- Delivery utama adalah aplikasi web; native mobile belum termasuk scope.
- Target arsitektur mengikuti ADR-0001 dan ADR-0002.
- Detail deployment, availability, kapasitas, browser support, dan disaster recovery belum boleh diklaim sebelum memiliki baseline serta approval.
- Detail authorization ditetapkan melalui work item keamanan tersendiri.

## Keberhasilan Baseline Produk

Baseline dinilai sehat ketika:

1. setiap capability aktif mempunyai requirement dan acceptance yang dapat ditelusuri;
2. setiap perubahan perilaku mempunyai work item dan bukti verifikasi;
3. fakta implementasi, keputusan produk, asumsi, serta kandidat tindak lanjut tidak bercampur;
4. tidak ada target modul, jadwal, kualitas, atau readiness yang diklaim tanpa bukti;
5. dokumen historis tetap dapat digunakan untuk menelusuri asal keputusan.

## Pertanyaan dan Keputusan Lanjutan

- Prioritas rilis belum ditetapkan.
- Persona serta responsibility matrix rinci belum disetujui.
- Target performa, kapasitas, availability, reliability, maintainability, dan compatibility belum dibaselining.
- Integrasi eksternal dan employee self-service belum disetujui.

Seluruh item tersebut berada di backlog `FTR-PROD-001` dan bukan requirement aktif.

## Persetujuan

```yaml
gate: product-baseline-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - capability bisnis menjadi unit scope
  - jumlah modul hanya fakta arsitektur
  - bukti kode tidak otomatis menjadi requirement
  - target tanpa baseline tidak menjadi komitmen aktif
evidence:
  - interview dan konfirmasi eksplisit pada FTR-PROD-001
```
