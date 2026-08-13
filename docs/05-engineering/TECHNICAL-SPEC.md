---
id: ENG-TECH-001
title: Spesifikasi Teknis Aktual dan Target
document_type: engineering-baseline
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: FTR-ENG-001
related: [ADR-0001, ADR-0002, API-SPEC-001, ENG-TEST-001]
---

# Spesifikasi Teknis Aktual dan Target

## Tujuan dan Cara Membaca

Dokumen ini menetapkan baseline engineering aktif. Setiap pernyataan dibedakan menjadi:

- **aktual**: dibuktikan oleh repository atau command pada 2026-08-13;
- **target**: telah disetujui tetapi diterapkan secara incremental;
- **deferred**: bukan target engineering aktif dan memerlukan work item tersendiri;
- **rekomendasi**: arah yang belum menjadi quality gate.

Dokumen lama tetap menjadi bukti historis. Klaim lama yang tidak didukung bukti tidak berlaku hanya karena pernah ditulis.

## Stack Aktual

Versi constraint berasal dari manifest. Versi resolved adalah snapshot lock file atau package terpasang saat audit, bukan janji upgrade otomatis.

### Backend

| Komponen | Constraint | Resolved | Peran |
|---|---|---|---|
| PHP | ^8.2 | runtime lokal 8.4.16 | runtime |
| Laravel Framework | ^12.0 | 12.62.0 | framework aplikasi |
| Inertia Laravel | ^2.0 | 2.0.24 | adapter HTTP Laravel–Inertia |
| Spatie Permission | ^8.0 | 8.0.0 | permission dan role |
| Spatie Media Library | ^11.23 | 11.23.0 | pengelolaan media |
| PHPUnit | ^11.5.3 | 11.5.55 | test backend |
| Laravel Pint | ^1.18 | 1.29.3 | format PHP |

Pest, PHPStan, dan Laravel Sanctum tidak tercatat pada dependency aktif.

### Frontend

| Komponen | Constraint manifest | Versi terpasang saat audit | Peran |
|---|---|---|---|
| React / React DOM | ^19.0.0 | 19.0.0 | UI |
| Inertia React | ^2.0.0 | 2.0.3 | adapter React–Inertia |
| TypeScript | ^5.7.2 | 5.7.3 | pemeriksaan tipe |
| Tailwind CSS | ^4.0.0 | 4.0.8 | styling |
| Vite | ^6.0 | 6.4.3 | build |
| Vitest | ^4.1.10 | 4.1.10 | test frontend |
| ESLint | ^9.17.0 | 9.39.5 | lint JavaScript/TypeScript |
| Prettier | ^3.4.2 | 3.5.2 | format frontend |
| React Testing Library | ^16.3.2 | 16.3.2 | test komponen |

Komponen UI lain pada package.json adalah dependency implementasi, bukan komitmen bahwa versinya selalu “latest”.

## Interface dan Authentication

### Aktual

- Interface aplikasi adalah web berbasis Laravel, Inertia, dan React.
- Guard default memakai web dengan session authentication.
- Bootstrap mendaftarkan routes/web.php, routes/auth.php, dan route modul; tidak mendaftarkan routes/api.php.
- Snapshot php artisan route:list --json pada 2026-08-13 menemukan 191 route, 178 route dengan middleware auth, serta tidak menemukan prefix api atau api/v1.
- CSRF, validation, middleware, policy, dan permission yang sudah ada tetap mengikuti perilaku implementasi. Detail authorization dikendalikan oleh AUTHORIZATION-MATRIX.md dan work item security terkait.

### Deferred

Public API, versioning /api/v1, token authentication, dan Laravel Sanctum bukan target engineering aktif. Penambahannya memerlukan requirement, kontrak, security review, compatibility plan, dan work item yang disetujui.

## Struktur Aplikasi

### Aktual

Kode modul berada di app/Modules/{Boundary}/{Module}/ dan masih dominan menggunakan struktur datar yang sudah ada. Struktur aktual adalah bukti perilaku, bukan struktur target.

### Target DDD-Lite

ADR-0001 adalah sumber kebenaran struktur target. Lokasi baku hanya dibuat sesuai kebutuhan nyata:

    app/Modules/{Boundary}/{Module}/
    ├── Application/
    ├── Domain/
    ├── Infrastructure/
    ├── Presentation/
    ├── Integration/
    │   └── Contracts/
    ├── Database/
    ├── Routes/
    ├── Tests/
    └── module.php

Domain/ hanya hadir bila modul memiliki aturan domain nyata. Integration/ hanya hadir bila ada kontrak atau event lintas modul. Folder, namespace, dan test dipindahkan bersama vertical slice modul secara incremental; dokumen ini tidak mengesahkan pemindahan massal.

Shell teknis HR/IntegrationContracts bukan modul bisnis target. Transisinya dikendalikan oleh ADR-0002 dan DEP-HR-001, bukan oleh baseline ini.

## Environment dan Driver

| Concern | Default repository saat audit | Batas interpretasi |
|---|---|---|
| Database local | SQLite pada .env.example | bukan desain database production |
| Database test | SQLite in-memory pada phpunit.xml | khusus test |
| Session | driver database pada .env.example; array saat test | bukan keputusan topologi production |
| Cache | driver database pada .env.example; array saat test | Redis belum menjadi baseline production |
| Queue | driver database pada .env.example; sync saat test | worker production belum ditetapkan |
| Filesystem | disk local | object storage/S3 deferred |
| Broadcast | log | delivery real-time deferred |

composer dev menjalankan server lokal, queue:listen --tries=1, Laravel Pail, dan Vite secara bersamaan. Ini adalah pengalaman development repository, bukan deployment design.

Production/staging topology, Redis production, S3, worker orchestration, monitoring/error tracking, health gate, backup, rollout, dan rollback operational harus diputuskan melalui work item operasional/deployment tersendiri.

## Database dan Identifier

- Akses data aktual memakai Laravel/Eloquent dan schema yang ada.
- Naming dan ownership schema mengikuti DATABASE-DESIGN.md.
- Primary key bigint yang ada tetap dipertahankan.
- ULID berstatus deferred dan hanya dapat ditangani oleh MIG-ID-001; baseline ini tidak mengubah identifier, foreign key, atau migration.

## Perintah Repository Aktif

| Tujuan | Command |
|---|---|
| Development backend/frontend | composer dev |
| Test backend | composer test atau php artisan test |
| Quality backend | composer quality:check |
| Validasi manifest modul | php artisan module:validate |
| Development frontend | npm run dev |
| Build frontend | npm run build |
| Build frontend + SSR | npm run build:ssr |
| Test frontend | npm run test:frontend |
| Lint tanpa perbaikan otomatis | npm run lint:check |
| Format check | npm run format:check |
| Type check | npm run typecheck |
| Quality frontend agregat | npm run quality:check |

npm run lint dan npm run format melakukan perubahan otomatis; keduanya bukan command verifikasi read-only.

## Standar Implementasi Aktif

Aturan berikut mempunyai bukti config atau pola repository yang konsisten:

- PHP diformat dengan Laravel Pint preset laravel.
- TypeScript memakai strict dan noImplicitAny.
- JavaScript/TypeScript diperiksa oleh ESLint dan frontend diformat oleh Prettier.
- Import frontend memakai alias @/* sesuai tsconfig.json.
- React memakai functional components dan hooks sebagai pola implementasi saat ini.
- ES modules digunakan oleh toolchain frontend.

strict_types=1, final secara default, readonly untuk seluruh DTO/value object, named arguments, PHPDoc untuk seluruh public method, JSDoc untuk seluruh export, dan larangan inline style tidak menjadi quality gate aktif karena belum ditegakkan secara konsisten oleh config atau baseline kode. Aturan tersebut boleh diusulkan melalui work item standardisasi terpisah.

## Quality, Security, dan Performance

Requirement aktif tetap mewajibkan keamanan, keandalan, performa, maintainability, accessibility, dan compatibility secara proporsional terhadap perubahan. Namun:

- tidak ada persentase coverage global yang telah disetujui;
- tidak ada angka response time, page load, query time, memory, atau concurrent user yang telah disetujui;
- tidak ada rate limit numerik global yang ditetapkan oleh dokumen ini;
- tidak ada klaim production-ready dari keberhasilan test lokal.

Target numerik hanya menjadi gate setelah dibaselining, disetujui, dan dipromosikan melalui work item kualitas/performa/operasional.

## CI Aktual dan Gap

- .github/workflows/tests.yml menjalankan install, build, module validation, dan PHPUnit.
- .github/workflows/lint.yml menjalankan Pint, ESLint, Prettier check, dan TypeScript check.
- Workflow memantau branch develop dan main, sedangkan branch pengembangan repository saat audit adalah dev.
- Workflow belum menjalankan Vitest dan belum membuktikan coverage.

Perbedaan branch dan cakupan command adalah gap yang harus diputuskan pada work item CI. Dokumen ini tidak mengubah workflow secara diam-diam.

## Batas Perubahan

### Selalu

- pertahankan perilaku aplikasi kecuali work item menyatakan perubahan eksplisit;
- bedakan keadaan aktual, target, deferred, dan rekomendasi;
- jalankan pemeriksaan terfokus sebelum memperluas scope;
- catat command dan output aktual sebagai bukti.

### Memerlukan Persetujuan/Work Item

- public API atau kontrak lintas sistem;
- authentication, authorization, dan security policy;
- dependency atau runtime service baru;
- database, identifier, dan migration;
- workflow CI, deployment, atau production topology;
- standard baru yang dinaikkan menjadi quality gate.

### Dilarang

- menganggap folder target sudah diimplementasikan;
- menyatakan tooling yang tidak terpasang sebagai aktif;
- mengubah perilaku melalui pekerjaan dokumentasi;
- menggunakan angka kualitas/performa yang belum disetujui sebagai acceptance.

## Keterbatasan Baseline

Snapshot versi, jumlah route, dan jumlah test dapat berubah setelah work item implementasi berikutnya. Setiap perubahan harus menyinkronkan baseline terkait sesuai DOCUMENTATION-SYNC-MATRIX.md.
