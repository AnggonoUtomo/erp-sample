---
id: TRACE-001
title: Matriks Keterlacakan Produk dan Implementasi
document_type: traceability-matrix
status: active
version: 2.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [DOC-PROJECT-BRIEF, PRD-001, SCOPE-001, REQ-CATALOG-001, ADR-0001, ADR-0002]
---

# Matriks Keterlacakan Produk dan Implementasi

## Cara Membaca

- `approved` pada requirement berarti keputusan produk aktif.
- Bukti kode menunjukkan perilaku yang ditemukan saat rekonsiliasi, bukan klaim bahwa seluruh acceptance telah diverifikasi runtime.
- Nama area kode dapat berubah melalui restrukturisasi/rename; capability ID tetap stabil.
- Status implementasi `observed` berarti surface ditemukan melalui inspeksi statis. Status tersebut bukan `verified` atau `production-ready`.

## Outcome ke Capability

| Outcome | Capability core/supporting |
|---|---|
| `OUT-001` | `CAP-HR-001`, `CAP-HR-002` |
| `OUT-002` | `CAP-HR-003`, `CAP-HR-004`, `CAP-HR-005`, `CAP-HR-006` |
| `OUT-003` | `CAP-DOC-001`, `CAP-DOC-002` |
| `OUT-004` | `CAP-ADM-001`, `CAP-AUD-001`, `CAP-CFG-001`, `CAP-OPS-001` |
| `OUT-005` | `CAP-EXP-001`, `CAP-RPT-001` |

## Capability ke Requirement dan Bukti

| Capability | Scope | Requirement | Bukti kode saat rekonsiliasi | Status implementasi |
|---|---|---|---|---|
| `CAP-HR-001` | core | `FR-001`, `BR-001`, `BR-002` | `app/Modules/HR/Employees` | observed |
| `CAP-HR-002` | core | `FR-006` | area Departements, OrganizationStructures, Positions, WorkLocations, EmploymentStatuses, EmploymentTypes, JobLevels, HRReferenceData | observed |
| `CAP-HR-003` | core | `FR-005`, `BR-002` | `app/Modules/HR/EmployeeContracts` | observed |
| `CAP-HR-004` | core | `FR-002`, `BR-002` | `app/Modules/HR/Onboardings` | observed |
| `CAP-HR-005` | core | `FR-013`, `BR-002` | `app/Modules/HR/EmployeeMovements` | observed |
| `CAP-HR-006` | core | `FR-003`, `BR-002` | `app/Modules/HR/Offboardings` | observed |
| `CAP-DOC-001` | core | `FR-004`, `BR-003`, `BR-004` | `app/Modules/HR/EmployeeDocuments` dan integration gateway DocumentManagement | observed |
| `CAP-DOC-002` | core | `FR-007`, `BR-004` | `app/Modules/DocumentManagement/Foundation` | observed |
| `CAP-ADM-001` | supporting | `FR-008`, `FR-014`, `NFR-004` | area AccessControls dan UserManagements | observed |
| `CAP-AUD-001` | supporting | `FR-009`, `NFR-004` | area AuditLogs dan LoginActivities | observed |
| `CAP-CFG-001` | supporting | `FR-015` | area SystemSettings dan NotificationTemplates | observed |
| `CAP-OPS-001` | supporting | `FR-016`, `NFR-002`, `NFR-006` | area BackupRestores, QueueMonitors, SchedulerMonitors | observed |
| `CAP-EXP-001` | supporting | `FR-017`, `BR-005` | area GlobalSearches dan ActivityCenters | observed |
| `CAP-RPT-001` | supporting | `FR-010`, `BR-005` | `app/Modules/HR/HRReports` serta command expiry read-only | observed |

## Capability Deferred

| Capability | Requirement | Bukti pendukung yang tidak cukup untuk approval implementasi | Status |
|---|---|---|---|
| `CAP-NOT-001` | `FR-011` | template notifikasi dan activity surface tersedia; trigger/delivery otomatis bisnis belum dibuktikan | deferred |
| `CAP-SELF-001` | `FR-018` | user/profile dan auth tersedia; portal/use case employee belum disetujui | deferred |
| `CAP-INT-001` | `FR-019` | kontrak internal tersedia; tidak ada target integrasi eksternal yang disetujui | deferred |
| `CAP-I18N-001` | belum ada | konfigurasi localization bukan bukti kebutuhan multi-language end-to-end | deferred |
| `CAP-RT-001` | belum ada | tidak ada requirement delivery real-time yang disetujui | deferred |

## Non-Functional Traceability

| Requirement | Berlaku untuk | Gate/bukti yang diwajibkan | Target numerik |
|---|---|---|---|
| `NFR-001` | flow yang terdampak perubahan | performance baseline/report bila relevan | menunggu `CAND-QUAL-001` |
| `NFR-002` | runtime/operasi | deployment, monitoring, rollback, dan availability evidence | menunggu `CAND-QUAL-001`/operational work item |
| `NFR-003` | data/query/workload bertumbuh | capacity/load evidence bila target disetujui | menunggu `CAND-QUAL-001` |
| `NFR-004` | seluruh input, data privat, session, dan aksi sensitif | security review, authorization baseline, test | menunggu `CAND-SEC-001` untuk detail kontrol |
| `NFR-005` | seluruh perubahan | ADR/boundary, test, lint/static check, review | tanpa persentase sampai dibaselining |
| `NFR-006` | mutation, migration, backup/restore | transaction/failure/recovery evidence | menunggu `CAND-QUAL-001` dan operational work item |
| `NFR-007` | flow UI | frontend/accessibility review proporsional | menunggu baseline kualitas |
| `NFR-008` | public/internal contract dan environment aktif | compatibility/deprecation/migration evidence | menunggu compatibility matrix |

## Keputusan Arsitektur dan Data

| Concern | Sumber kebenaran aktif | Bukti implementasi saat audit | Status |
|---|---|---|---|
| Struktur DDD-Lite | ADR-0001 | struktur modul masih dominan datar | target accepted; implementasi bertahap melalui `ARC-DDD-LITE-001` |
| Katalog modul | `MODULE-CATALOG.md` | manifest module ditemukan pada kode | fakta arsitektur; bukan requirement produk |
| Ownership kontrak integrasi | ADR-0002 | kontrak tersebar pada modul bisnis dan shell pusat | accepted; transisi melalui `DEP-HR-001` |
| Identifier database | `DATABASE-DESIGN.md`, `MIG-ID-001` | bigint aktif | ULID deferred dan di luar baseline produk |
| Authorization | `AUTHORIZATION-MATRIX.md`, `CAND-SEC-001` | policy/permission tersebar pada module | detail baseline belum direkonsiliasi |
| Interface HTTP | `API-SPEC.md` dan route module | route internal berbasis auth ditemukan | runtime/full contract belum diverifikasi pada work item ini |

## Work Item Terkait

| Work item | Scope | Status pada 2026-08-13 |
|---|---|---|
| `FTR-PROD-001` | rekonsiliasi baseline produk dan requirement | completed |
| `ARC-DDD-LITE-001` | struktur DDD-Lite seluruh module | in_progress; tidak ada task coding aktif |
| `DEP-HR-001` | deprecation shell `HR/IntegrationContracts` | approved, belum ready |
| `MIG-ID-001` | evaluasi/migrasi primary key ke ULID | deferred |

## Keterbatasan Bukti

- Rekonsiliasi memakai inspeksi statis terhadap manifest, route, service, command, dan page; bukan pengujian runtime penuh.
- Keberadaan route tidak membuktikan seluruh acceptance, authorization, keamanan, performa, atau readiness production.
- Gap implementasi atau perilaku tanpa nilai bisnis dipromosikan melalui work item baru; tidak diperbaiki di sini.
