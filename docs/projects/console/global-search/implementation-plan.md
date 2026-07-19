# Implementation Plan: Console Global Search / Command Palette

## Overview

Implementasi dibuat incremental. Kita mulai dari command palette shell, lalu navigation search dari shared Inertia props, kemudian safety guard, lalu provider entity backend pertama.

## Architecture decisions

- MVP memakai `navigation` dari shared props sebagai sumber result awal.
- Tidak ada migration/database table untuk command palette.
- Permission-aware filtering dari metadata navigation adalah UX layer; backend route tujuan tetap menjadi security boundary.
- Entity search masa depan wajib memakai provider contract backend, bukan query besar langsung di frontend.
- Provider entity pertama adalah `Users`, dibuka sebagai JSON endpoint read-only dan belum dikonsumsi frontend command palette.

## Phase 1 — Navigation search foundation

### Task 01 — Command palette shell dan shortcut

Membuat dialog/palette global yang bisa dibuka dari header search dan `Ctrl/Cmd+K`, tetapi result masih empty/instruction state.

### Task 02 — Navigation result provider

Flatten shared navigation menjadi result searchable, termasuk nested child menu, group, title translation, dan URL.

### Checkpoint A — Search shell usable

Palette bisa dibuka, ditutup, dan mencari menu yang user boleh akses.

## Phase 2 — Usability dan safety

### Task 03 — Keyboard navigation dan deterministic ranking

Tambahkan navigasi keyboard, ranking sederhana, highlight selected item, dan behavior enter/escape.

### Task 04 — Permission dan forbidden result guard

Tambahkan test/helper agar result tanpa permission tidak muncul dan result tidak membawa key sensitif.

### Checkpoint B — Safe navigation command palette

Global search menu/navigation siap dipakai tanpa membuka data sensitif.

## Phase 3 — Backend/entity extension

### Task 05 — Provider contract draft untuk entity search

Dokumentasikan kontrak backend untuk provider entity search masa depan tanpa mengaktifkan query entity. Output Task 05 adalah spec/ADR/plan update saja:

- `EntitySearchProvider`;
- `SearchQuery`;
- `SearchContext`;
- `SearchResult`;
- forbidden-field guard;
- auth, permission, rate-limit, result-limit, dan error boundary.

Task ini **tidak** membuat route/controller/service aktif, migration, atau query database entity.

### Task 06 — Users read-only provider spike

Selesai 2026-07-19. Provider entity pertama yang dibuka adalah `Users` dengan permission ketat, no sensitive fields, deterministic limit, dan rate limit.

Keputusan provider pertama:

- `Users` dipilih karena project ini berada di namespace Console dan paling dekat dengan identity lookup.
- `Employees` tetap deferred agar HR entity search tidak dibuka sebelum boundary privacy HR dibahas.

Provider `Users` membawa test auth denial, global permission denial, provider permission denial, forbidden-field guard, rate limit, validasi query, dan deterministic limit.

Frontend command palette tetap navigation-only sampai integrasi entity result di-approve sebagai task terpisah.

### Final quality checkpoint

Pastikan format, typecheck, build, test frontend, module validation, dan test backend relevan hijau.

## Risks and mitigations

| Risiko                                                       | Dampak                                    | Mitigasi                                                                               |
| ------------------------------------------------------------ | ----------------------------------------- | -------------------------------------------------------------------------------------- |
| Search menampilkan menu tanpa permission                     | User bingung dan potensi disclosure route | Filter dari metadata permission dan route tetap policy-gated                           |
| Entity search membocorkan PII                                | Security/privacy issue                    | Provider contract, allowlist output, forbidden-field guard, dan provider permission    |
| Provider berbeda mengembalikan shape result berbeda          | Frontend rapuh dan sulit diuji            | Gunakan `SearchResult` DTO tunggal dan aggregator yang memvalidasi output              |
| Search endpoint jadi terlalu mahal                           | Beban database dan potensi abuse          | Rate limit, minimal query length, max result limit, provider timeout/failure isolation |
| Command palette berubah jadi mutation launcher terlalu cepat | Risiko destructive action                 | MVP read-only navigation saja                                                          |
| Keyboard shortcut bentrok input form                         | UX mengganggu saat mengetik               | Shortcut global diabaikan saat target aktif input/textarea/contenteditable             |
| Result ranking tidak deterministik                           | Test flaky dan UX membingungkan           | Ranking sederhana exact > prefix > contains, lalu sort title                           |

## Verification checkpoints

Checkpoint A:

```bash
npm run typecheck
npm run build
git diff --check
```

Checkpoint B:

```bash
npm run typecheck
npm run test:frontend
npm run build
php artisan module:validate
git diff --check
```

Task 06:

```bash
php artisan test --filter=ConsoleGlobalSearchTest
php artisan module:validate
npm run typecheck
npm run build
git diff --check
```

Final:

```bash
npm run format:check
npm run lint:check
npm run typecheck
npm run test:frontend
npm run build
php artisan module:validate
php artisan test
git diff --check
```

## Open questions before frontend entity integration

- Apakah entity result `Users` boleh ditampilkan di command palette UI, atau endpoint backend cukup disiapkan dulu?
- Apakah result entity boleh menampilkan email, atau tetap hanya nama + internal URL seperti spike saat ini?
- Apakah provider berikutnya `Employees`, atau command palette cukup navigation + users sampai Console UX matang?
