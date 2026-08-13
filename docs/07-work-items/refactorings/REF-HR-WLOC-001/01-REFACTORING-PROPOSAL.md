---
id: DOC-REF-HR-WLOC-001-PROPOSAL
title: Proposal Refactoring Pilot DDD-Lite HR WorkLocations
document_type: refactoring-proposal
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ARC-DDD-LITE-001, ADR-0001, FTR-ENG-001]
---

# Proposal Refactoring Pilot DDD-Lite HR WorkLocations

## Tujuan

Memindahkan backend dan test yang dimiliki modul HR/WorkLocations dari susunan datar saat ini ke lokasi baku DDD-Lite ADR-0001, dengan hanya menghadirkan folder yang benar-benar dibutuhkan dan tanpa mengubah perilaku aplikasi.

## Masalah yang Diselesaikan

Modul masih memakai lokasi DTO/, Http/, Models/, Policies/, Providers/, Services/, Transactions/, dan routes.php di root modul. Susunan tersebut tidak sesuai sumber kebenaran aktif ADR-0001 dan belum menyediakan bukti bahwa discovery route serta test runner dapat menerima struktur target secara incremental.

## Klasifikasi

CRITICAL. Pekerjaan tidak mengubah kontrak publik, data, atau semantics authorization, tetapi menyentuh namespace policy serta registrasi Gate di samping cutover lintas beberapa consumer dan tooling bersama. Sesuai aturan memilih tingkat tertinggi yang berlaku, jalur authorization menjadikannya CRITICAL dan memerlukan persetujuan manusia serta bukti authorization terfokus.

## Struktur Target Minimal

| Concern | Lokasi saat ini | Lokasi target |
|---|---|---|
| DTO | DTO/WorkLocationData.php | Application/DTOs/WorkLocationData.php |
| service aplikasi | Services/WorkLocationsService.php | Application/Services/WorkLocationsService.php |
| model Eloquent | Models/WorkLocation.php | Infrastructure/Models/WorkLocation.php |
| provider Laravel | Providers/WorkLocationsServiceProvider.php | Infrastructure/Providers/WorkLocationsServiceProvider.php |
| transaction database | Transactions/WorkLocationsTransaction.php | Infrastructure/Transactions/WorkLocationsTransaction.php |
| controller | Http/Controllers/WorkLocationsController.php | Presentation/Http/Controllers/WorkLocationsController.php |
| request Laravel | Http/Requests/*.php | Presentation/Http/Requests/*.php |
| policy Laravel/Spatie | Policies/WorkLocationPolicy.php | Presentation/Policies/WorkLocationPolicy.php |
| route web | routes.php | Presentation/Routes/web.php |
| test milik modul | tests/Feature/HRWorkLocationTest.php | app/Modules/HR/WorkLocations/Tests/Feature/HRWorkLocationTest.php |

Database/, module.php, navigation.php, permissions.php, dan Support/Permissions.php tetap pada lokasi saat ini. Folder Domain/ tidak dibuat karena tidak ditemukan aturan domain murni yang perlu dipisahkan. Folder Integration/ tidak dibuat karena pilot tidak menambah atau memindahkan kontrak/event lintas modul.

## Keputusan Desain

- ModuleRegistry dan ModuleContractValidator menerima Presentation/Routes/web.php sebagai target utama serta routes.php sebagai fallback selama transisi; keduanya tidak dimuat bersamaan.
- Semua consumer namespace model diperbarui pada cutover yang sama. Alias atau shim namespace lama dilarang.
- Policy berada di Presentation/Policies karena merupakan adapter authorization Laravel/Spatie untuk operasi controller dan bergantung pada model User.
- WorkLocationsTransaction berada di Infrastructure/Transactions karena hanya membungkus DB::transaction Laravel; ia bukan aturan domain. Wrapper dan semantics-nya dipertahankan.
- Refactor bersifat struktural murni: tidak membuat entity domain, repository, action, atau integration contract baru.
- Test yang hanya dimiliki WorkLocations dipindahkan ke modul. Test arsitektur, bootstrap, dan lintas sistem tetap berada di tests/.
- Penyesuaian generator DDD-Lite, redesign coupling lintas modul, dan konsolidasi export permission adalah pekerjaan terpisah.

## Requirement dan Kriteria Penerimaan

| ID | Requirement / kriteria |
|---|---|
| REF-WLOC-REQ-001 | Enam route web/session, nama route, HTTP verb, URI, middleware policy, binding, dan respons Inertia tetap ekuivalen. |
| REF-WLOC-REQ-002 | Validasi request, operasi CRUD/restore/force-delete, soft delete, filter, pagination, summary, map settings, dan audit event tetap ekuivalen. |
| REF-WLOC-REQ-003 | Tabel, migration, primary key, foreign key, relasi, permission key, role mapping, navigation, dan UI tidak berubah. |
| REF-WLOC-REQ-004 | Struktur target mengikuti ADR-0001 secara minimal tanpa Domain/ atau Integration/ kosong. |
| REF-WLOC-REQ-005 | Route discovery target-first/fallback berlaku umum dan tidak mengandung shim khusus WorkLocations. |
| REF-WLOC-REQ-006 | Semua import namespace lama yang terdampak diperbarui tanpa alias kompatibilitas. |
| REF-WLOC-REQ-007 | PHPUnit menemukan test milik modul secara additive; test lintas sistem tidak dipindahkan. |
| REF-WLOC-REQ-008 | Focused test, consumer regression test, module validation, quality check, dan pemeriksaan diff lulus sebelum review. |

Requirement non-fungsional:

| ID | Requirement |
|---|---|
| REF-WLOC-NFR-001 | Kompatibilitas perilaku dan data harus dipertahankan tanpa shim namespace lama. |
| REF-WLOC-NFR-002 | Struktur harus lebih mudah ditemukan sesuai ADR-0001 tanpa folder/abstraksi spekulatif. |
| REF-WLOC-NFR-003 | Perubahan harus dapat diverifikasi dan direvert per task; cutover model menjadi satu unit atomik. |
| REF-WLOC-NFR-004 | Authorization semantics dan permission key tidak boleh berubah. |
| REF-WLOC-NFR-005 | Tidak boleh ada perubahan query/algoritma yang disengaja atau klaim performa tanpa pengukuran. |

## Scope

- Struktur dan namespace backend HR/WorkLocations.
- Consumer langsung kelas WorkLocation yang harus tetap valid.
- Discovery route dan validasi kontrak modul yang diperlukan untuk struktur target.
- Discovery PHPUnit untuk test lokal modul.
- Dokumentasi, evidence, review, dan baseline sync yang diwajibkan SEOS.

## Stakeholder dan Pengguna Terdampak

- Pemilik proyek sebagai pemberi keputusan dan penerima hasil.
- Maintainer Console, HR, serta tooling modul sebagai pihak yang harus memperbarui import dan discovery.
- Administrator HR yang memakai pengelolaan work location melalui web/session.
- Consumer Employees, EmployeeMovements, HRReports, dan Console Dashboard yang memakai model atau tabel WorkLocations.

## Non-Scope

- Fitur atau perubahan perilaku bisnis.
- Migration, schema, data, primary key, atau ULID.
- Public API, token authentication, atau kontrak eksternal.
- Penghapusan HR/IntegrationContracts.
- Perubahan kontrak AuditLogs atau SystemSettings.
- Redesign direct cross-module model/table access.
- Perubahan generator modul.
- Perubahan frontend HR WorkLocations.

## Persetujuan

| Gate | Keputusan | Pemberi keputusan | Tanggal | Kondisi |
|---|---|---|---|---|
| refactoring-scope-approval | approved | Pemilik proyek | 2026-08-14 | Perilaku dipertahankan; route discovery target-first dengan fallback; namespace lama diputus serentak tanpa shim; test modul dipindah incremental; policy berada di Presentation. |
| authorization-refactor-approval | approved | Pemilik proyek | 2026-08-14 | Hanya lokasi namespace policy dan wiring Gate yang berubah; permission key, role mapping, middleware, dan keputusan allow/deny harus tetap. |

Persetujuan diberikan melalui interview keputusan teknis dalam percakapan. Tidak diperlukan ADR baru karena struktur target sudah diputuskan oleh ADR-0001 dan pekerjaan ini tidak mengubah boundary atau kontrak publik.

## Dampak Security, Privacy, dan Trust Boundary

Trust boundary tetap berupa request web/session terautentikasi, middleware policy, controller, FormRequest, lalu persistence. Aset yang dijaga adalah hak operasi administratif dan data work location. Risiko utama pilot adalah elevation of privilege atau denial yang salah bila Gate menunjuk class yang keliru, serta tampering bila validasi ikut berubah. Mitigasinya adalah mempertahankan middleware/permission secara exact, menguji allow dan deny 403, membuktikan Gate mapping, dan melarang perubahan request rule. Tidak ada data sensitif, endpoint, role, secret, dependency, atau aliran privasi baru.

## Keterlacakan

- Induk: ARC-DDD-LITE-001.
- Mewujudkan: TSK-ARC-DDD-LITE-001-02.
- Baseline engineering: FTR-ENG-001.
- Sumber keputusan: ADR-0001.
