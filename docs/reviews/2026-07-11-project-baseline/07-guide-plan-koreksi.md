# 07 — Guide Plan Koreksi

## P0 — Pulihkan kepercayaan baseline

Kerjakan CR-01 dan CR-02. Jangan memulai refactor besar selama suite nondeterministic. Exit: CP-1.

## P1 — Jadikan aturan dapat dieksekusi

Kerjakan CR-05, CR-06, CR-04 melalui TASK-03–05. Pisahkan formatting-only dari behavior. Exit: CP-2.

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

