# Implementation Plan: HR Reports

## Overview

HR Reports dibangun sebagai module read-only yang membaca data dari module HR yang sudah ada. Implementasi dilakukan secara incremental agar setiap slice bisa diuji tanpa mencampur mutation lifecycle. Seeder lifecycle HR dibuat setelah report query pertama stabil, supaya data uji mengikuti kontrak report yang benar.

## Architecture decisions

- `HRReports` adalah consumer read-only, bukan owner data.
- Semua report menerima tanggal eksplisit agar reproducible.
- MVP memakai query langsung ke tabel/source module resmi; snapshot/reporting table ditunda sampai volume data menuntut.
- Export Excel/PDF ditunda sampai list/detail report stabil.
- Seeder lifecycle HR dibuat idempotent dan diberi namespace data seed agar aman di local/dev.

## Dependency map

```txt
HR foundation masters
  Departements, Positions, JobLevels, WorkLocations,
  EmploymentStatuses, EmploymentTypes, HRReferenceData
        │
        ├── Employees
        │     ├── Headcount by Departement
        │     ├── Headcount by Work Location
        │     └── Employment Status Summary
        │
        ├── Employee Contracts
        │     └── Contract Expiry
        │
        ├── Employee Documents
        │     └── Document Expiry
        │
        ├── Employee Movements
        │     └── Future report/history context
        │
        ├── Onboardings / Offboardings
        │     └── Future new hire/exit report
        │
        └── HR Reports
              ├── Read-only services
              ├── Inertia page
              ├── Read-only commands
              └── Lifecycle seeder for report data
```

## Phase 1 — Contract dan module shell

Tujuan: membuat module `HRReports` terdaftar dengan permission, navigation, route read-only, dan test authorization dasar.

Checkpoint:

- Module terdeteksi oleh `php artisan module:validate`.
- Tidak ada route mutation.
- User tanpa permission ditolak.

## Phase 2 — Headcount vertical slice

Tujuan: membangun report paling aman dulu, yaitu headcount by Departement, Work Location, dan Employment Status.

Langkah:

1. Buat service/query headcount dengan tanggal eksplisit.
2. Buat controller/page Inertia read-only.
3. Buat frontend compact report cards/tables.
4. Test group count, empty state, filter, dan authorization.

Checkpoint:

- Headcount report bisa dibuka dan hasilnya deterministic.
- Tidak ada mutation/audit/notification side effect.

## Phase 3 — Expiry reports

Tujuan: menampilkan risk report untuk Contract Expiry dan Document Expiry.

Langkah:

1. Konsumsi query/semantics yang sudah ada dari Employee Contracts dan Employee Documents bila tersedia.
2. Pastikan output tidak membawa field sensitif.
3. Tambahkan command read-only untuk expiry summary.
4. Test boundary expired/expiring/valid dan empty result.

Checkpoint:

- Contract/document expiry report lulus test boundary.
- Payload lulus sensitive-data review.

## Phase 4 — Seeder lifecycle HR

Tujuan: membuat data local/dev yang realistis untuk satu lifecycle HR end-to-end.

Seeder minimal:

- Departement: HR, Finance, Operations, IT.
- Work location: Head Office, Remote, Branch.
- Employment status: Active, Probation, Resigned/Terminated.
- Employment type: Permanent, Contract, Internship.
- Employees: beberapa employee aktif/probation/exit.
- Contracts: active, expiring soon, expired/ended.
- Documents: valid, expiring soon, expired, no-expiry.
- Movements: transfer/promotion/employment change applied.
- Onboardings: completed/active sample.
- Offboardings: completed/cancelled sample.

Checkpoint:

- Seeder idempotent.
- Report MVP memiliki data non-kosong.
- Seeder tidak membuat data sensitif production.

## Phase 5 — Quality checkpoint

Tujuan: memastikan HR Reports aman untuk dilanjutkan ke export/report advanced.

Command:

```bash
vendor/bin/pint --test
php artisan module:validate
npm run format:check
npm run lint:check
npm run typecheck
npm run build
php artisan test
git diff --check
```

Checkpoint:

- Semua quality gates hijau.
- Docs sesuai behavior aktual.
- Export dan downstream integration tetap deferred.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---:|---|
| Report diam-diam menjadi mutation path | High | ADR read-only, route inventory test, no POST/PATCH/DELETE |
| Headcount semantics ambigu untuk resigned/archived | Medium | Filter status eksplisit dan open question disetujui sebelum coding |
| Payload expiry bocor nomor dokumen/path file | High | Sensitive-data test dan DTO minimal |
| Query lambat saat data besar | Medium | MVP query langsung; snapshot/reporting table deferred dengan gate |
| Seeder mengganggu data manual | Medium | Namespace kode seed, idempotent upsert, hanya local/dev/manual |

## Open questions before implementation

1. Default headcount menghitung status apa saja?
2. Seeder lifecycle HR dijalankan manual atau ikut local database seeder?
3. Apakah UI report pertama cukup tabel/card, atau perlu chart sederhana?
