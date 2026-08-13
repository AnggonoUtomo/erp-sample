---
id: SPEC-FTR-ENG-001
title: Spesifikasi Rekonsiliasi Baseline Engineering
document_type: feature-specification
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: FTR-ENG-001
related: [CAND-DOC-002, AUD-003, ADR-0001, ADR-0002]
---

# Spesifikasi Rekonsiliasi Baseline Engineering

## Problem

TECHNICAL-SPEC.md dan TESTING-STRATEGY.md mencampur kondisi aktual, target DDD-Lite, contoh fiktif, tooling yang tidak terpasang, serta target kualitas yang belum disetujui. Baseline tersebut dapat mengarahkan implementasi berikutnya pada asumsi yang salah.

## Outcome

Baseline aktif menjawab stack, command, struktur, interface, authentication, driver, testing, CI, dan batasan berdasarkan fakta repository serta keputusan eksplisit.

## Stakeholder

- Pemilik proyek sebagai approver.
- Developer/AI yang menggunakan baseline untuk work item berikutnya.
- Reviewer arsitektur, quality, security, dan operasional.

## Keputusan Hasil Interview

1. Interface aktif tetap web/session berbasis Laravel dan Inertia. Public API, token authentication, dan Sanctum berstatus deferred.
2. Baseline hanya mencatat default repository: SQLite untuk local/test, database-backed session/cache/queue pada .env.example, local filesystem, dan Composer dev command. Data tersebut bukan production design.
3. Tidak ada pemindahan test massal. Test arsitektur, bootstrap, dan lintas sistem boleh tetap di tests/; test milik satu modul mengikuti ADR-0001 secara incremental.
4. Quality gate aktif hanya aturan yang dibuktikan config atau pola konsisten. Aturan lain dicatat sebagai rekomendasi/kandidat.
5. Target coverage, performa, reliability, dan operasional numerik deferred sampai mempunyai work item dan persetujuan.
6. Gap CI antara branch develop/main dan branch aktif dev dicatat untuk keputusan terpisah, bukan diperbaiki pada work item dokumentasi ini.

## Scope

- Rekonsiliasi docs/05-engineering/TECHNICAL-SPEC.md.
- Rekonsiliasi docs/05-engineering/TESTING-STRATEGY.md.
- Sinkronisasi traceability, implementation plan, paket ARC terdampak, registry, backlog, dan inventory.
- Pemeriksaan command yang tersedia dan pencatatan hasil aktual.

## Non-Scope

- Kode aplikasi, test, config, workflow CI, dependency, schema, atau runtime behavior.
- Public API atau token authentication baru.
- Authorization matrix dan security baseline rinci.
- Production deployment/runbook dan target kualitas numerik.
- Migrasi struktur DDD-Lite, HR/IntegrationContracts, atau ULID.

## Requirement Work Item

| ID | Requirement |
|---|---|
| ENG-REQ-001 | Baseline membedakan aktual, target, deferred, dan rekomendasi. |
| ENG-REQ-002 | Interface aktif dan authentication dicatat sesuai route/config/dependency. |
| ENG-REQ-003 | Default local/test tidak dinyatakan sebagai desain production. |
| ENG-REQ-004 | Lokasi test aktual dan target incremental dijelaskan tanpa migrasi massal. |
| ENG-REQ-005 | Hanya command/tooling/standard yang tersedia atau terbukti dinyatakan aktif. |
| ENG-REQ-006 | Target numerik yang belum disetujui tidak menjadi quality gate. |
| ENG-REQ-007 | Gap di luar scope dicatat tanpa perubahan aplikasi/config/CI. |

## Risiko dan Edge Case

- Lock file dan node_modules dapat berbeda dari constraint manifest; keduanya harus disebut sebagai snapshot.
- Timeout runner tidak boleh otomatis ditafsirkan sebagai kegagalan assertion.
- Keberhasilan test lokal tidak membuktikan CI atau production readiness.
- Struktur target tidak boleh ditulis seolah sudah menjadi struktur aktual.
- Dokumen security/operasional yang belum direkonsiliasi tidak boleh diisi diam-diam.

## Kriteria Penerimaan

- [x] ENG-REQ-001 sampai ENG-REQ-007 tercermin pada baseline.
- [x] Klaim API v1, Sanctum, PHPStan, Pest, module-local test aktual, angka coverage, angka performa, dan deployment production lama dihapus sebagai baseline aktif.
- [x] ADR-0001 tetap menjadi target DDD-Lite utama dengan folder minimal sesuai kebutuhan.
- [x] Tidak ada file aplikasi/config/test/dependency/CI yang berubah.
- [x] Command verifikasi dan limitation dicatat dengan hasil aktual.
- [x] Dokumen historis tidak dihapus atau ditulis ulang.

## Persetujuan

Pemilik proyek menyetujui seluruh rumusan keputusan melalui interview satu pertanyaan per concern pada 2026-08-13.
