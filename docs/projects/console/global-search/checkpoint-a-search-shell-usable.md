# Checkpoint A — Search Shell Usable

Tanggal: 2026-07-19  
Status: **Pass**

Checkpoint ini menutup Task 01–02 untuk `Console Global Search / Command Palette`.

## Scope yang selesai

- Header search berubah dari placeholder statis menjadi trigger command palette.
- `Ctrl+K` dan `Cmd+K` membuka command palette.
- Shortcut diabaikan saat user sedang mengetik di input, textarea, select, atau contenteditable.
- `Escape` menutup command palette.
- Shared `navigation` diflatten menjadi result searchable.
- Parent menu dan nested/child menu ikut searchable.
- Title asli, title Bahasa Indonesia, group, group Bahasa Indonesia, dan URL menjadi keyword.
- Result mengikuti permission user dari shared Inertia props.
- Klik result menutup palette dan melakukan navigasi Inertia.

## Review kualitas

### Correctness

Task 01 dan Task 02 memenuhi acceptance criteria. Search result sudah diuji untuk nested navigation, permission filtering, dan keyword dari title/group/URL.

### Maintainability

Search logic dipisah ke helper pure `resources/js/lib/navigation-search.ts`, sehingga ranking/guard berikutnya bisa diuji tanpa bergantung ke React atau browser.

### Security

MVP tetap read-only dan navigation-first. Tidak ada backend endpoint baru, tidak ada entity/database search, dan tidak ada mutation command. Result yang memiliki metadata permission hanya muncul jika user punya salah satu permission tersebut. Route tujuan tetap wajib dilindungi backend policy/middleware.

### UX

Command palette sudah punya empty/instruction state Bahasa Indonesia dan result list sederhana. Keyboard selection/ranking lanjut masuk Task 03 agar scope tetap kecil.

## Evidence

```bash
npm run typecheck
npm run test:frontend
npm run build
git diff --check
```

Hasil:

- `npm run typecheck` — pass.
- `npm run test:frontend` — pass, 12 file / 23 test.
- `npm run build` — pass.
- `git diff --check` — pass.

## Follow-up

- Task 03: keyboard navigation `ArrowUp`/`ArrowDown`/`Enter` dan deterministic ranking.
- Task 04: forbidden result guard untuk memastikan keyword/result sensitif ditolak atau disanitasi.
- Entity/database search tetap deferred sesuai ADR-001.
