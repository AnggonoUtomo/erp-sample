# 04 — Baseline Specification

## Requirements

- REQ-01: seluruh automated gate harus deterministic dan dapat dijalankan non-mutating.
- REQ-02: test filesystem/module generator harus parallel-safe.
- REQ-03: manifest module menjadi sumber contract; command validator melaporkan path, field, dependency, export, dan slug invalid.
- REQ-04: generator menghasilkan namespace/path/acronym konsisten.
- REQ-05: setiap mutation route mempunyai authentication, authorization, validation, dan test denial.
- REQ-06: full restore mempunyai threat model, format/version validation, resource limits, audit, pre-restore recovery point, dan failure semantics.
- REQ-07: frontend page mengikuti composer pattern dan mempunyai accessibility/test strategy.
- REQ-08: dokumentasi menghubungkan finding → requirement → task → verification.

## Non-scope

- Implementasi atau refactor kode dalam audit ini.
- Redesign UI, perubahan schema/domain HR, deployment, dan migration production.
- Penetration test serta sertifikasi compliance.

## Struktur target (referensi)

```text
app/Modules/{Project}/{Module}/
  DTO/ Http/ Integrations/ Listeners/ Policies/ Providers/
  Services/ Support/ Transactions/
  module.php navigation.php permissions.php routes.php
resources/js/pages/{project-slug}/{module-slug}/
  index.tsx types.ts options.ts {module}-components/
tests/Feature/Modules/{Project}/{Module}/
tests/Unit/Support/Modules/
```

File opsional harus dinyatakan melalui `exports` di manifest; struktur target bukan alasan membuat folder kosong tanpa fungsi.

## Command design

- `php artisan make:module {Project}:{Module} [--without-frontend] [--force]`: menghasilkan path canonical dan gagal aman bila target ada.
- `php artisan module:validate [Project.Module] [--json]`: read-only; exit 0 valid, exit 1 violation; tidak memperbaiki otomatis.
- `composer test` (perlu ditambahkan): suite PHP tunggal.
- `npm run lint:check`, `npm run format:check`, `npm run typecheck`, `npm run build`: seluruhnya non-mutating kecuali command berakhiran `:fix`.

## Acceptance criteria

- AC-01: full PHP suite hijau minimal 3 run serial dan 3 run parallel.
- AC-02: generator tests tidak menulis/menghapus directory source global yang dipakai test lain.
- AC-03: validator menangkap missing contract, duplicate key/slug, dependency tidak ditemukan/cycle, dan acronym path drift.
- AC-04: semua quality check exit 0 dari clean checkout dan tidak mengubah `git status`.
- AC-05: malicious/oversized/traversal ZIP dan SQL invalid ditolak sebelum destructive write.
- AC-06: restore failure menghasilkan audit evidence dan recovery path yang teruji.
- AC-07: canonical frontend pages lolos typecheck, format, accessibility interaction test yang disepakati.

## Test plan

- Unit: manifest schema, graph dependency, slug/acronym normalization, SQL/ZIP validator.
- Feature: generator success/conflict/force, authorization matrix, restore happy/failure/rollback.
- Integration: registry discovery dari isolated fixture root; Media Library fake disk lifecycle.
- Frontend: component interaction, keyboard/focus, permission-hidden/disabled actions, error state.
- Non-functional: parallel test, archive limits, build bundle observation, clean-tree assertion.

## Keputusan dokumentasi tahap ini

Spec berstatus **Proposed** dan perlu persetujuan manusia sebelum implementasi. Ia tidak mengubah contract production saat ini.

