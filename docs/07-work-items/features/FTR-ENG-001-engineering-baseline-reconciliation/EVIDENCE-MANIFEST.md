---
id: EVD-FTR-ENG-001
title: Manifest Bukti Rekonsiliasi Baseline Engineering
document_type: evidence-manifest
status: verified
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: FTR-ENG-001
related: [CAND-DOC-002, ENG-TECH-001, ENG-TEST-001]
---

# Manifest Bukti — FTR-ENG-001

    work_item: FTR-ENG-001
    source_commit: ea64df02ab15273c9279130ddd1e22a48829d7e1
    commit_or_pr: null
    evidence_date: 2026-08-13
    result: verified
    files_changed:
      - docs/00-governance/FILE-INVENTORY.md
      - docs/02-requirements/TRACEABILITY-MATRIX.md
      - docs/05-engineering/TECHNICAL-SPEC.md
      - docs/05-engineering/TESTING-STRATEGY.md
      - docs/06-planning/IMPLEMENTATION-PLAN.md
      - docs/07-work-items/WORK-ITEM-REGISTRY.md
      - docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/CONTEXT-PACK.md
      - docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/PLAN.md
      - docs/07-work-items/architecture-changes/ARC-SEOS-WORK-001/BACKLOG.md
      - docs/07-work-items/features/FTR-ENG-001-engineering-baseline-reconciliation/*

## Bukti Audit

| Concern | Bukti | Hasil |
|---|---|---|
| Backend dependency | composer.json/composer.lock | Laravel 12.62.0, Inertia Laravel 2.0.24, PHPUnit 11.5.55; Sanctum/Pest/PHPStan tidak tersedia |
| Frontend dependency | package.json/package-lock.json/node_modules manifest | React 19.0.0, Inertia React 2.0.3, Vite 6.4.3, Vitest 4.1.10, TypeScript 5.7.3 |
| Route/interface | php artisan route:list --json | 191 route; 178 dengan middleware auth; 0 prefix api |
| Authentication | config/auth.php, bootstrap/app.php, route registration | guard web/session; tidak ada route API bootstrap |
| Default environment | .env.example dan phpunit.xml | SQLite local/test; database session/cache/queue pada contoh env; local filesystem; test memakai array/sync |
| Test inventory | filesystem dan phpunit.xml | 106 file PHP: 101 Feature, 5 Unit; 0 module-local; 12 file frontend |
| CI | .github/workflows/tests.yml dan lint.yml | trigger develop/main; branch kerja dev; Vitest tidak dijalankan workflow |
| Coverage lokal | extension PHP | PCOV/Xdebug tidak tersedia pada runner lokal |

## Command dan Hasil Aktual

| Command | Exit | Durasi | Ringkasan |
|---|---:|---:|---|
| php artisan module:validate | 0 | tercakup quality backend | seluruh module contract valid |
| composer quality:check | 0 | 124,6 detik | module validation dan Pint lulus; 523 test / 3.256 assertion lulus dalam 86,86 detik |
| npm run lint:check | 0 | 92,4 detik | ESLint lulus |
| npm run format:check | 0 | 25,1 detik | seluruh file resources sesuai Prettier |
| npm run typecheck | 0 | 27,1 detik | tsc --noEmit lulus |
| npm run test:frontend | 0 | 18,45 detik | 12 file / 28 test lulus saat dijalankan terisolasi |
| npm run build | 0 | 32,5 detik | Vite build lulus; 2.204 modul ditransformasi |
| php artisan route:list --json | 0 | 16,1 detik dalam audit gabungan | ringkasan route terurai benar |

## Pemeriksaan Gagal atau Terbatas

- npm list dengan daftar package melewati timeout 30 detik; versi terpasang kemudian dibaca dari manifest package di node_modules dan lockfile.
- npm run quality:check agregat melewati timeout runner sekitar 303,6 detik dan berakhir EPIPE; tidak ada kesimpulan assertion failure dari kejadian ini.
- Percobaan menjalankan format, typecheck, test, dan build secara paralel menghasilkan tiga unhandled worker timeout pada Vitest. Rerun npm run test:frontend secara terisolasi lulus 12 file/28 test.
- PCOV/Xdebug tidak tersedia pada runtime lokal, sehingga coverage tidak diukur.
- Workflow GitHub tidak dijalankan pada work item lokal ini; hasil lokal tidak dinyatakan sebagai bukti CI.

## Interpretasi

Semua pemeriksaan terpisah yang diwajibkan lulus. Timeout agregat/paralel dicatat sebagai limitation runner dan kandidat CAND-TEST-001. Tidak ada perubahan code/config/test/dependency/workflow untuk mengatasi limitation dalam work item dokumentasi ini.

## Pemeriksaan Dokumentasi dan Scope

| Pemeriksaan | Hasil |
|---|---|
| inventory terhadap filesystem | 290 aktual; 290 tercatat; 0 missing; 0 extra |
| path perubahan | 22 path; seluruhnya di bawah docs/ |
| paket work item | 13 file; seluruhnya memiliki frontmatter dan metadata status/version |
| code fence | 0 dokumen dengan fence ganjil |
| klaim lama terlarang pada dua baseline | 0 match; penyebutan tooling/API lama hanya dalam konteks tidak tersedia/deferred |
| secret-like assignment pada diff | 0 match |
| git diff --check | lulus; tidak ada output |
