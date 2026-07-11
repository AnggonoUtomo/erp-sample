# 07 — Guide Plan Koreksi

Status eksekusi terakhir: 2026-07-12. Urutan tetap risk-first; item berstatus selesai tidak dikerjakan ulang.

## Status eksekusi

| Tahap | Status | Evidence utama |
| --- | --- | --- |
| P0 / CP-1 | Selesai | TASK-01–02 pada `d20aa3f`; suite penuh 170 test/475 assertions pada verifikasi 2026-07-12. |
| P1 / CP-2 | Selesai | TASK-04–05 pada `d20aa3f`; TASK-03 ditutup pada slice 2026-07-12 dengan regression test acronym, penghapusan page legacy, typecheck, dan build hijau. |
| P2 / CP-3 | Selesai dengan batas ADR-004 | Authorization denial coverage dan full-backup v2 hardening pada `d20aa3f`; checksum bukan authenticity signature lintas environment. |
| P3 / CP-4 | Selesai | BackupRestore (`5145c47`), SystemSetting (`6cd4374`), dan page composer terbesar (`28bad32`) dipecah incremental. |

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
