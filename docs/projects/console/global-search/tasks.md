# Tasks: Console Global Search / Command Palette

Task ditulis kecil dan incremental. MVP awal fokus ke search menu/navigation, bukan data entity.

## Task 01 — Command palette shell dan shortcut ✅

**Tujuan:** membuat UI command palette global yang bisa dibuka dari search box header dan shortcut keyboard.

**Files yang disentuh:**

- `resources/js/components/app-menu-header.tsx`
- `resources/js/components/global-command-palette.tsx`
- `docs/projects/console/global-search/tasks.md`

**Acceptance criteria:**

- [x] Search box header bisa diklik untuk membuka palette.
- [x] `Ctrl+K` dan `Cmd+K` membuka palette.
- [x] `Escape` menutup palette.
- [x] Shortcut tidak aktif saat user sedang mengetik di input/textarea/contenteditable.
- [x] Empty/instruction state berbahasa Indonesia.

**Hasil:** selesai 2026-07-19. Header search sekarang membuka `GlobalCommandPalette`, shortcut `Ctrl+K`/`Cmd+K` aktif untuk user login, shortcut diabaikan saat user sedang mengetik di input/textarea/select/contenteditable, dan palette punya instruction state berbahasa Indonesia. Result search menu belum diaktifkan karena masuk Task 02.

**Cara test:**

```bash
npm run typecheck
npm run build
git diff --check
```

**Dependencies:** none. **Scope:** S.

## Task 02 — Navigation result provider ✅

**Tujuan:** mengubah shared `navigation` menjadi result searchable.

**Files yang disentuh:**

- `resources/js/lib/navigation-search.ts`
- `resources/js/components/global-command-palette.tsx`
- `resources/js/components/app-menu-header.tsx`
- `resources/js/test/navigation-search.test.ts`
- `docs/projects/console/global-search/tasks.md`

**Acceptance criteria:**

- [x] Menu parent dan child/nested ikut searchable.
- [x] Title asli dan title Bahasa Indonesia masuk keyword.
- [x] Group/kategori masuk keyword.
- [x] Result punya `id`, `title`, `group`, `url`, dan `keywords`.
- [x] Result yang tidak punya permission user tidak muncul.

**Hasil:** selesai 2026-07-19. `navigation-search.ts` mengubah shared `navigation` menjadi command palette result yang permission-aware, mendukung child/nested menu, title/group translation, keyword dari title asli/terjemahan/group/URL, dan result internal URL. `GlobalCommandPalette` sekarang menampilkan result navigation dari permission user dan klik result melakukan navigasi Inertia. Entity/database search belum dibuat sesuai ADR-001.

**Cara test:**

```bash
npm run typecheck
npm run test:frontend
npm run build
git diff --check
```

**Dependencies:** Task 01. **Scope:** M.

## Checkpoint A — Search shell usable ✅

- [x] Task 01–02 selesai.
- [x] Palette bisa membuka dan mencari menu yang boleh diakses.
- [x] Tidak ada backend endpoint baru.
- [x] Tidak ada data sensitif masuk result.

**Hasil checkpoint:** selesai 2026-07-19. Ringkasan tersedia di [Checkpoint A — Search Shell Usable](checkpoint-a-search-shell-usable.md). Command palette sudah aktif dari header dan shortcut, search navigation sudah permission-aware, child/nested menu ikut searchable, dan entity/database search tetap deferred sesuai ADR-001.

**Evidence:**

```bash
npm run typecheck
npm run test:frontend
npm run build
git diff --check
```

**Hasil evidence:**

- `npm run typecheck` — pass.
- `npm run test:frontend` — pass, 12 file / 23 test.
- `npm run build` — pass.
- `git diff --check` — pass.

## Task 03 — Keyboard navigation dan deterministic ranking ✅

**Tujuan:** membuat command palette nyaman dipakai keyboard dan ranking search konsisten.

**Files yang disentuh:**

- `resources/js/components/global-command-palette.tsx`
- `resources/js/lib/navigation-search.ts`
- `resources/js/test/navigation-search.test.ts`
- `docs/projects/console/global-search/tasks.md`

**Acceptance criteria:**

- [x] `ArrowDown` dan `ArrowUp` memilih result.
- [x] `Enter` membuka result terpilih.
- [x] Ranking deterministik: exact > prefix > contains > fallback alphabetical.
- [x] Query kosong menampilkan instruksi atau result prioritas terbatas.
- [x] Empty state jelas saat tidak ada match.

**Hasil:** selesai 2026-07-19. Command palette sekarang mendukung keyboard selection dengan `ArrowDown`/`ArrowUp`, wrapping selection, `Enter` untuk membuka result terpilih, dan reset selection saat query berubah. Ranking search dibuat deterministik: exact match lebih tinggi dari prefix match, prefix lebih tinggi dari contains, lalu fallback sort berdasarkan title/group. Query kosong tetap menampilkan result prioritas terbatas dari navigation yang boleh diakses.

**Cara test:**

```bash
npm run typecheck
npm run test:frontend
npm run build
git diff --check
```

**Dependencies:** Checkpoint A. **Scope:** M.

## Task 04 — Permission dan forbidden result guard ✅

**Tujuan:** memperkuat guard agar result command palette tidak membocorkan menu/data yang tidak layak.

**Files yang disentuh:**

- `resources/js/lib/navigation-search.ts`
- `resources/js/test/navigation-search.test.ts`
- `docs/projects/console/global-search/tasks.md`

**Acceptance criteria:**

- [x] Helper permission result diuji untuk exact permission dan permission array.
- [x] Result tanpa URL internal ditolak.
- [x] Result dengan field/keyword sensitif ditolak atau disanitasi.
- [x] Test memastikan secret/token/password/api_key tidak muncul di result.

**Hasil:** selesai 2026-07-19. Navigation search sekarang hanya memasukkan URL internal yang aman, tetap permission-aware untuk exact permission maupun permission array, dan fail-closed saat title/group/url/badge/keyword mengandung istilah sensitif seperti `secret`, `token`, `password`, atau `api_key`. Guard ini menjaga command palette tetap menjadi navigation shortcut, bukan jalur bocor metadata sensitif.

**Cara test:**

```bash
npm run typecheck
npm run test:frontend
npm run build
git diff --check
```

**Dependencies:** Task 03. **Scope:** S.

## Checkpoint B — Safe navigation command palette ✅

- [x] Task 03–04 selesai.
- [x] Command palette siap dipakai untuk navigasi read-only.
- [x] Permission-aware result sudah diuji.
- [x] Forbidden result guard sudah diuji.

**Hasil checkpoint:** selesai 2026-07-19. Ringkasan tersedia di [Checkpoint B — Safe Navigation Command Palette](checkpoint-b-safe-navigation-command-palette.md). Command palette sudah punya keyboard navigation, ranking deterministik, permission-aware result, internal URL guard, dan forbidden-result guard. Entity/database search tetap deferred sesuai ADR-001.

**Evidence:**

```bash
npm run typecheck
npm run test:frontend
npm run build
php artisan module:validate
git diff --check
```

## Task 05 — Provider contract draft untuk entity search ✅

**Tujuan:** menyiapkan kontrak backend untuk entity search masa depan tanpa mengaktifkan query entity.

**Files yang disentuh:**

- `docs/projects/console/global-search/specification.md`
- `docs/projects/console/global-search/implementation-plan.md`
- `docs/projects/console/global-search/decisions/002-entity-search-provider-contract.md`
- `docs/projects/console/global-search/README.md`
- `docs/projects/console/global-search/tasks.md`

**Acceptance criteria:**

- [x] Contract provider entity search terdokumentasi.
- [x] Forbidden fields dan privacy guard jelas.
- [x] Permission/rate-limit/auth boundary jelas.
- [x] Tidak ada backend entity search aktif tanpa approval.

**Hasil:** selesai 2026-07-19. Entity search provider contract diformalkan di [ADR-002: Entity search provider contract](decisions/002-entity-search-provider-contract.md) dan diringkas di specification/implementation plan. Task ini hanya dokumentasi kontrak: belum ada route/controller/service/provider aktif, belum ada migration, dan belum ada query entity. Implementasi provider pertama tetap menunggu approval Task 06.

**Cara test:**

```bash
git diff --check
```

**Dependencies:** Checkpoint B. **Scope:** S.

## Task 06 — Users read-only provider spike ✅

**Tujuan:** mulai provider entity kecil untuk `Users` secara read-only.

**Files yang disentuh:**

- `app/Modules/Console/GlobalSearches/module.php`
- `app/Modules/Console/GlobalSearches/routes.php`
- `app/Modules/Console/GlobalSearches/permissions.php`
- `app/Modules/Console/GlobalSearches/Contracts/EntitySearchProvider.php`
- `app/Modules/Console/GlobalSearches/DTO/SearchContext.php`
- `app/Modules/Console/GlobalSearches/DTO/SearchQuery.php`
- `app/Modules/Console/GlobalSearches/DTO/SearchResult.php`
- `app/Modules/Console/GlobalSearches/Http/Controllers/GlobalSearchController.php`
- `app/Modules/Console/GlobalSearches/Http/Requests/GlobalSearchRequest.php`
- `app/Modules/Console/GlobalSearches/Providers/UserSearchProvider.php`
- `app/Modules/Console/GlobalSearches/Services/GlobalSearchService.php`
- `app/Modules/Console/GlobalSearches/Support/ForbiddenSearchResultGuard.php`
- `app/Modules/Console/GlobalSearches/Support/Permissions.php`
- `tests/Feature/ConsoleGlobalSearchTest.php`
- `docs/projects/console/global-search/tasks.md`

**Acceptance criteria:**

- [x] Provider hanya membaca entity yang disetujui.
- [x] Route auth-protected, permission-aware, rate-limited.
- [x] Result tidak memuat PII/sensitive field di luar scope.
- [x] Limit result kecil dan deterministik.
- [x] Test denial matrix dan forbidden-field guard hijau.

**Hasil:** selesai 2026-07-19. Provider pertama yang dibuka adalah `Users` karena paling dekat dengan boundary Console identity. Endpoint `GET /global-search` aktif sebagai JSON endpoint read-only dengan permission `global-search.search`, rate limit `30 request / menit`, validasi query, dan provider-level permission `users.view`. Provider bisa mencari user dari nama/email, tetapi response hanya mengembalikan title nama, URL internal ke User Management, badge read-only, dan `userId`; email, password, remember token, role/permission payload, dan field sensitif tidak dikirim. Frontend command palette tetap navigation-only sampai integrasi entity result di-approve terpisah.

**Cara test:**

```bash
php artisan test --filter=ConsoleGlobalSearch
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

**Dependencies:** Task 05 approval. **Scope:** M.

## Final quality checkpoint ✅

- [x] Semua task MVP selesai.
- [x] Search navigation read-only siap digunakan.
- [x] Relevant tests hijau.
- [x] README/spec/plan/tasks/ADR sesuai implementasi aktual.

**Hasil checkpoint:** selesai 2026-07-22. Ringkasan tersedia di [Final Quality Checkpoint](final-quality-checkpoint.md). Navigation command palette siap digunakan, backend `Users` provider spike sudah tersedia sebagai endpoint read-only, dan semua quality gates final hijau. Frontend command palette tetap navigation-only sampai integrasi entity result di-approve sebagai follow-up.

**Evidence final:**

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
