# Console Global Search / Command Palette

Dokumen ini mendefinisikan project kecil `Console Global Search / Command Palette` sebagai lanjutan dari [Console Project](../README.md).

Fitur ini mengaktifkan kolom search di header Console. Target MVP bukan mesin pencarian semua data, tetapi **navigasi cepat yang aman**: user bisa mencari menu, halaman, dan aksi baca yang memang boleh ia akses.

## Status

`Final checkpoint pass — 2026-07-22`.

Command palette navigation MVP sudah melewati Checkpoint B. Entity search backend spike pertama sudah aktif untuk provider `Users` sebagai endpoint read-only yang permission-aware, tetapi frontend command palette masih navigation-only sampai integrasi entity result di-approve terpisah.

## Urutan baca

1. [Specification](specification.md) — requirement, non-scope, boundary, struktur folder, command design, acceptance criteria, dan test plan.
2. [ADR-001: Permission-aware navigation search first](decisions/001-permission-aware-navigation-search-first.md) — alasan MVP dimulai dari search menu/navigation, bukan full entity search.
3. [ADR-002: Entity search provider contract](decisions/002-entity-search-provider-contract.md) — kontrak provider entity search masa depan, forbidden fields, auth, permission, rate-limit, dan error boundary.
4. [Implementation plan](implementation-plan.md) — urutan implementasi incremental dari search navigation sampai optional entity provider.
5. [Tasks](tasks.md) — task kecil dengan tujuan, file yang disentuh, acceptance criteria, dan cara test.
6. [Checkpoint A — Search Shell Usable](checkpoint-a-search-shell-usable.md) — verifikasi command palette shell dan navigation result provider.
7. [Checkpoint B — Safe Navigation Command Palette](checkpoint-b-safe-navigation-command-palette.md) — verifikasi keyboard navigation, ranking, permission guard, URL guard, dan forbidden-result guard.
8. [Final Quality Checkpoint](final-quality-checkpoint.md) — verifikasi final navigation search, backend `Users` provider spike, quality gates, dan boundary follow-up.

## Relasi lintas dokumen

- [Console module guide](../module-guide.md) — aturan umum module Console, route, permission, frontend, backend, dan quality gate.
- [Console roadmap](../roadmap.md) — global search tercatat sebagai follow-up non-blocking Console.
- [Console final quality checkpoint](../final-quality-checkpoint.md) — search/help placeholder tercatat sebagai follow-up setelah Console baseline selesai.
- [Project module guide global](../../../guides/project-module-guide.md) — aturan project/module repository.

## Bentuk MVP

MVP dianggap cukup jika:

- shortcut `Ctrl+K` / `Cmd+K` membuka command palette;
- search dapat mencari menu/sidebar/navigation yang sudah user boleh akses;
- hasil search tidak menampilkan menu tanpa permission;
- klik result melakukan navigasi Inertia ke halaman tujuan;
- tidak ada mutation data;
- tidak ada data sensitif seperti password, token, secret, document number, atau backup content masuk result;
- empty state dan keyboard navigation mudah dipahami user pemula.

## Non-scope MVP

- Search isi database semua module.
- Search dokumen DMS/full text.
- Search audit payload detail.
- Global action mutation seperti delete/restore/backup/flush queue.
- Ranking berbasis analytics/personalization.
- Search lintas tenant atau lintas guard tanpa permission.

## Next step

Setelah final checkpoint, follow-up berikutnya adalah:

**Entity result frontend integration decision**

Putuskan apakah endpoint `GET /global-search` mulai dikonsumsi command palette UI. Jika iya, buat task terpisah untuk debounce, loading state, cancellation, dan frontend privacy guard.
