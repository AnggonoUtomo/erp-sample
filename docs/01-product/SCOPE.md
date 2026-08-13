---
id: SCOPE-001
title: Scope Produk 12erp
document_type: product-scope
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [DOC-PROJECT-BRIEF, PRD-001, REQ-CATALOG-001]
---

# Scope Produk 12erp

## Aturan Status Scope

| Status | Makna |
|---|---|
| `core` | nilai utama produk; perubahan atau penghapusannya memerlukan evaluasi produk |
| `supporting` | diperlukan untuk menjalankan, mengendalikan, atau mengoperasikan kapabilitas core |
| `deferred` | kandidat bernilai yang belum menjadi komitmen implementasi |
| `excluded` | secara eksplisit bukan tujuan baseline produk saat ini |

Status scope bukan status implementasi dan bukan urutan rilis. Bukti implementasi tersedia pada `TRACEABILITY-MATRIX.md`; urutan kerja ditentukan melalui work item.

## Core Scope

| Capability ID | Kapabilitas | Outcome |
|---|---|---|
| `CAP-HR-001` | Data induk pegawai | `OUT-001` |
| `CAP-HR-002` | Struktur dan referensi tenaga kerja | `OUT-001` |
| `CAP-HR-003` | Lifecycle kontrak kerja | `OUT-002` |
| `CAP-HR-004` | Onboarding pegawai | `OUT-002` |
| `CAP-HR-005` | Perubahan penugasan pegawai | `OUT-002` |
| `CAP-HR-006` | Offboarding pegawai | `OUT-002` |
| `CAP-DOC-001` | Dokumen pegawai dan kepatuhan | `OUT-003` |
| `CAP-DOC-002` | Lifecycle dokumen privat/perusahaan | `OUT-003` |

## Supporting Scope

| Capability ID | Kapabilitas | Outcome |
|---|---|---|
| `CAP-ADM-001` | User dan kontrol akses | `OUT-004` |
| `CAP-AUD-001` | Audit dan aktivitas keamanan | `OUT-004` |
| `CAP-CFG-001` | Konfigurasi dan template sistem | `OUT-004` |
| `CAP-OPS-001` | Operasi dan pemulihan sistem | `OUT-004` |
| `CAP-EXP-001` | Discovery dan aktivitas pengguna | `OUT-005` |
| `CAP-RPT-001` | Laporan HR read-only | `OUT-005` |

## Deferred Scope

| Capability ID | Kandidat | Syarat promosi minimum |
|---|---|---|
| `CAP-NOT-001` | Notifikasi bisnis otomatis | trigger, penerima, channel, timing, retry, privacy, dan acceptance disetujui |
| `CAP-SELF-001` | Employee self-service | persona, use case, data exposure, serta authorization disetujui |
| `CAP-INT-001` | Integrasi sistem eksternal | target, contract, ownership, security, dan failure mode disetujui |
| `CAP-I18N-001` | Multi-language | bahasa target, ownership konten, fallback, dan acceptance disetujui |
| `CAP-RT-001` | Notifikasi real-time | kebutuhan latency, channel, delivery guarantee, serta operasi disetujui |

Item `deferred` bukan requirement aktif dan tidak boleh diimplementasikan hanya karena terdapat library, template, atau kode pendukung.

## Excluded Scope

| Area | Alasan baseline |
|---|---|
| Payroll/penggajian | domain regulasi dan perhitungan tersendiri; belum diputuskan |
| Time and attendance | bukan capability aktif produk |
| Recruitment/ATS | bukan capability aktif produk |
| Performance management | bukan capability aktif produk |
| Learning management | bukan capability aktif produk |
| Finance/accounting | istilah ERP tidak menjadi janji domain ini |
| Procurement dan inventory | istilah ERP tidak menjadi janji domain ini |
| Sales dan CRM | istilah ERP tidak menjadi janji domain ini |
| Native mobile application | delivery aktif adalah web internal |
| Multi-tenant SaaS | konteks aktif adalah satu organisasi internal |

`excluded` dapat dievaluasi ulang melalui proposal scope baru; tabel ini bukan larangan permanen.

## Boundary Scope

- Produk digunakan di dalam satu organisasi.
- Boundary bisnis aktif adalah Console, HR, dan DocumentManagement sesuai `BOUNDARY-REGISTRY.md`.
- Nama boundary/module tidak digunakan sebagai capability ID.
- API eksternal tidak menjadi komitmen produk saat ini.
- Detail akses ditentukan authorization baseline; dokumen ini tidak memberikan permission.

## Perubahan Scope

Setiap promosi, penghapusan, atau perubahan makna capability wajib:

1. mempunyai work item terdaftar;
2. menjelaskan nilai, pengguna, dampak, risiko, dependensi, dan acceptance;
3. memperbarui PRD, Requirements, serta Traceability;
4. memperoleh persetujuan manusia bila menyentuh Human Decision Gate;
5. tidak menghapus bukti keputusan sebelumnya.

## Persetujuan

```yaml
gate: product-scope-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - status capability tidak menentukan urutan coding
  - prioritas rilis ditetapkan per work item
evidence:
  - SPEC-FTR-PROD-001
```
