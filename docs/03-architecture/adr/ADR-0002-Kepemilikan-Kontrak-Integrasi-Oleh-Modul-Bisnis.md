---
id: ADR-0002
title: Kepemilikan Kontrak Integrasi oleh Modul Bisnis
status: accepted
created: 2026-08-13
updated: 2026-08-13
deciders: [Pemilik proyek]
related: [ARC-DDD-LITE-001, DEP-HR-001]
---

# ADR-0002: Kepemilikan Kontrak Integrasi oleh Modul Bisnis

## Konteks

`app/Modules/HR/IntegrationContracts` adalah modul teknis tanpa tabel, route, navigasi, atau use case bisnis mandiri. Modul ini membungkus empat snapshot provider, DTO versioned, registry, privacy guard, event envelope, serta tiga command inspeksi.

Pencarian referensi PHP di luar folder modul menemukan consumer langsung hanya pada test. Tidak ditemukan production consumer yang mengimpor namespace `HR\IntegrationContracts`. Pada saat yang sama, modul `EmployeeContracts`, `EmployeeDocuments`, `EmployeeMovements`, `Employees`, dan `Offboardings` telah memiliki surface `Integration/` sendiri yang dipakai production code.

Keputusan historis `docs/projects/hr/integration-contracts/decisions/001-stable-hr-integration-contracts.md` pada commit `aab3c87a88ccdda64c95051ec72b431648e0ecdf` menetapkan kebutuhan kontrak HR yang versioned, minim PII, dan melarang consumer membaca model internal secara bebas. Prinsip tersebut tetap valid; yang dievaluasi ulang adalah kepemilikan shell modul teknisnya.

## Pendorong Keputusan

1. Modul harus mewakili tanggung jawab bisnis yang mandiri.
2. Kontrak lebih mudah dipelihara bila dimiliki sumber data dan aturan bisnisnya.
3. Satu modul registry pusat menciptakan ketergantungan ke banyak modul HR dan menduplikasi surface yang sudah ada.
4. Proteksi PII dan kompatibilitas versi harus tetap dipertahankan.

## Opsi yang Dipertimbangkan

### Opsi A: Pertahankan `HR/IntegrationContracts`

- Manfaat: satu namespace untuk semua consumer HR.
- Biaya/risiko: modul teknis menjadi pusat coupling dan tidak mempunyai tanggung jawab bisnis mandiri.

### Opsi B: Pindahkan kontrak ke modul bisnis pemilik

- Manfaat: ownership jelas, perubahan schema dekat dengan aturan sumber, dan sesuai target DDD-Lite.
- Biaya/risiko: namespace consumer berubah dan kontrak yang tampak serupa harus direkonsiliasi semantiknya.

### Opsi C: Pindahkan semua kontrak ke `app/Integration`

- Manfaat: shell modul HR dapat dihapus.
- Biaya/risiko: kontrak bisnis kehilangan pemilik dan root integration berisiko menjadi tempat bersama tanpa batas.

## Keputusan

Pilih Opsi B dan gunakan `app/Integration` hanya untuk mekanisme teknis yang benar-benar generik dan digunakan oleh lebih dari satu modul.

Pemetaan target yang diusulkan:

| Surface saat ini | Pemilik target | Catatan perilaku yang harus dipertahankan |
|---|---|---|
| `EmployeeSnapshotProvider` + `EmployeeSnapshotV1` | `HR/Employees/Integration/` | Snapshot profil minimum dan minim PII. |
| `EmployeeAssignmentSnapshotProvider` + DTO | `HR/Employees/Integration/` | Saat ini membaca assignment aktif pada `hr_employees`; parameter tanggal belum menyediakan historical replay. |
| `EmployeeContractSnapshotProvider` + DTO | `HR/EmployeeContracts/Integration/` | Harus direkonsiliasi dengan `EmployeeContractSnapshotReader` yang sudah ada; semantics fallback tidak boleh berubah diam-diam. |
| `EmployeeDocumentComplianceSnapshotProvider` + DTO | `HR/EmployeeDocuments/Integration/` | Snapshot compliance tetap read-only dan mempertahankan aturan warning date. |
| Event HR versioned | Modul producer masing-masing | Event aktif tetap dimiliki `EmployeeMovements` dan `Offboardings`; event deferred tidak dianggap publisher aktif. |
| Registry kontrak/event dan command inspeksi | Katalog SEOS + architecture test | Bukan modul runtime bisnis. |
| `IntegrationEventEnvelopeV1` dan privacy guard | Belum diputuskan | Hanya dipindahkan ke `app/Integration` jika reuse nyata terbukti; selain itu tetap dekat producer/contract owner. |

Shell `HR/IntegrationContracts` dideprecate melalui `DEP-HR-001`, kemudian hanya boleh dihapus setelah seluruh consumer test dan binding berpindah serta readiness gate disetujui.

## Konsekuensi

### Positif

- Katalog modul hanya memuat kapabilitas yang mandiri.
- Kontrak dan aturan kompatibilitas berada dekat pemilik data.
- Prinsip versioning serta minimisasi PII dari keputusan lama tetap berlaku.

### Negatif

- Namespace dan binding berubah saat implementasi.
- Snapshot kontrak memiliki perbedaan semantics dengan reader yang sudah ada dan membutuhkan compatibility test.

### Netral / Tindak Lanjut

- Tidak ada kode yang dipindah sebelum `DEP-HR-001` berstatus `ready`.
- Tidak ada public REST API yang diciptakan sebagai bagian dari deprecation.
- Tidak ada kontrak generik yang dipindah ke Shared Kernel secara otomatis.

## Validasi

1. Inventaris consumer production dan test harus lengkap.
2. Contract test lama dipindahkan atau diadaptasi tanpa mengurangi assertion privacy/versioning.
3. Binding container baru menghasilkan data yang sama untuk fixture yang sama.
4. Tidak ada import namespace lama setelah compatibility window berakhir.
5. Module validator dan seluruh test suite lulus setelah shell dihapus.

## Penggantian

ADR ini menggantikan keputusan historis yang mewajibkan satu shell `HR Integration Contracts`, tetapi mempertahankan keputusan tentang kontrak versioned, additive change, date semantics, dan payload minim PII.

## Catatan Persetujuan

```yaml
gate: architecture-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - pemetaan ownership dan compatibility test direview sebelum coding
  - penghapusan aktual menunggu readiness DEP-HR-001
evidence:
  - arahan untuk menghapus modul teknis dan memindahkan kontrak ke modul bisnis pemilik
  - persetujuan eksplisit ADR-0002 melalui percakapan pada 2026-08-13
```
