# 07 — Guide Plan Koreksi

Status eksekusi terakhir: 2026-07-12. Urutan tetap risk-first; item berstatus selesai tidak dikerjakan ulang.

## Status eksekusi

| Tahap | Status | Evidence utama |
| --- | --- | --- |
| P0 / CP-1 | Selesai | TASK-01–02 pada `d20aa3f`; suite penuh 170 test/475 assertions pada verifikasi 2026-07-12. |
| P1 / CP-2 | Selesai | TASK-04–05 pada `d20aa3f`; TASK-03 ditutup pada slice 2026-07-12 dengan regression test acronym, penghapusan page legacy, typecheck, dan build hijau. |
| P2 / CP-3 | Pending global matrix | Full-backup v2 hardening selesai pada `d20aa3f`; denial coverage backup dan queue hijau, tetapi route×permission matrix seluruh mutation belum terdokumentasi lengkap. |
| P3 / CP-4 | Selesai | Tiga dekomposisi pada `5145c47`, `6cd4374`, `28bad32`; TASK-09 menambahkan 4 frontend characterization tests tanpa kebocoran ke production bundle. |

Detail requirement/task dan cara verifikasi tetap mengikuti [baseline spec](04-baseline-spec.md) serta [delivery plan](05-delivery-plan.md).

## P0 — Pulihkan kepercayaan baseline

Kerjakan CR-01 dan CR-02. Jangan memulai refactor besar selama suite nondeterministic. Exit: CP-1.

## P1 — Jadikan aturan dapat dieksekusi

Kerjakan CR-05, CR-06, CR-04 melalui TASK-03–05. Pisahkan formatting-only dari behavior. Exit: CP-2.

Catatan TASK-03: canonical path adalah `resources/js/pages/hr/hr-reference-data/`. Regression test berada di `MakeModuleCommandTest`; pencarian `h-r-reference-data` hanya boleh menemukan assertion negative test.

## P2 — Tutup risiko security/correctness

Bangun authorization matrix dan threat model restore sebelum mengubah implementasi. Kerjakan TASK-06–07 dengan rollback/runbook. Exit: CP-3.

## P3 — Kurangi biaya perubahan

Tambahkan characterization tests lalu pecah satu service/page per increment. Mulai dari BackupRestore, lalu SystemSetting, lalu page composer terbesar. Exit: CP-4.

## Definition of correction done

- Finding memiliki owner, severity, task, evidence, dan keputusan.
- Check hijau serta non-mutating dari clean checkout.
- Dokumentasi/manifest/generator tidak saling bertentangan.
- Tidak ada perbaikan lintas scope yang dicampur ke slice.
- Semua keputusan mahal-direverse dicatat ADR.

## Matrix penyelesaian finding

| Finding | Owner | Severity | Task | Evidence/keputusan |
| --- | --- | --- | --- | --- |
| CR-01 | Platform backend | Critical | TASK-01 | Selesai `d20aa3f`; fixture root unik. |
| CR-02 | Console/user backend | Required | TASK-02 | Selesai `d20aa3f`; media disk per process. |
| CR-03 | Backup/security | Required/Security | TASK-07 | Selesai dengan batas ADR-004; signature lintas environment non-scope baseline. |
| CR-04 | Tooling/CI | Required | TASK-04 | Selesai `d20aa3f`; check dan fix terpisah. |
| CR-05 | Module tooling/frontend | Required | TASK-03 | Selesai `460ff9f`; canonical acronym regression test. |
| CR-06 | Module platform | Required | TASK-05 | Selesai `d20aa3f`; manifest validator read-only. |
| CR-07 | Backup/settings/frontend | Maintainability | TASK-08, TASK-09 | Selesai; dekomposisi dan characterization frontend hijau. |
| CR-08 | Quality/security | Coverage | TASK-06, TASK-09, TASK-10 | Restore, frontend interaction, dan queue failure/retry selesai; global mutation denial matrix TASK-06 masih terbuka. |

## Pekerjaan lanjutan terbuka

### TASK-09 — Frontend interaction characterization

Status: **Selesai 2026-07-12**.

- Tujuan: membuktikan filter debounce, shortcut/focus, permission action, dan editor flow pada page composer HR Reference Data.
- File: konfigurasi test frontend minimum, hook/page test, dan dependency lockfile; tidak mengubah UI.
- Acceptance: test gagal bila shortcut/filter orchestration rusak; typecheck, lint, format, build, dan backend suite tetap hijau.
- Test: runner frontend headless, `npm run quality:check`, `composer quality:check`.

Evidence: Vitest/jsdom terintegrasi ke `quality:check`; 4 test mengunci debounce filter, shortcut/focus, permission action, dan editor hydration. Test disimpan di luar `pages/` agar resolver Inertia tidak memasukkannya ke production bundle.

### TASK-10 — Queue failure/retry evidence

Status: **Selesai 2026-07-12**.

- Tujuan: menutup sisa CR-08 untuk failure/retry queue tanpa mengubah kebijakan retry production kecuali defect terbukti.
- File: focused queue feature/unit test; production file hanya bila RED membuktikan defect.
- Acceptance: failure dan retry/terminal state mempunyai assertion deterministik serta authorization tetap hijau.
- Test: focused queue tests dan full backend suite.

Evidence: database queue worker memindahkan fixture deterministik ke `failed_jobs`; endpoint retry mengembalikannya ke `jobs`; retry, forget, dan flush menolak user tanpa permission.

### Sisa TASK-06 — Global mutation denial matrix

- Tujuan: inventaris seluruh mutation route dan buktikan guest serta authenticated user tanpa permission ditolak sebelum perubahan state.
- File: dokumen matrix dan focused feature tests per kelompok modul; production code hanya bila test menemukan defect.
- Acceptance: setiap POST/PUT/PATCH/DELETE memiliki owner permission dan evidence denial; tidak menerima 404 sebagai pengganti authorization untuk resource yang valid.
- Test: focused authorization tests per modul, lalu full backend suite.

### Concern dependency audit

`npm audit --omit=dev` pada 2026-07-12 melaporkan 13 advisory pada dependency tree lama (termasuk high/critical). Jangan menjalankan `npm audit fix` massal di dalam TASK-09; lakukan upgrade dependency sebagai perubahan terpisah dengan compatibility test dan review lockfile.
