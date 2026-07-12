# Baseline Review Laravel12 ERP

Status: baseline review dan koreksi P0–P3 selesai sampai CP-4. Audit awal dibuat 2026-07-11; status terakhir tersedia di [guide plan koreksi](07-guide-plan-koreksi.md#status-eksekusi).

## Urutan baca

1. [01-code-review.md](01-code-review.md) — kondisi aktual, temuan, dan quality gate.
2. [02-context-and-architecture.md](02-context-and-architecture.md) — peta folder serta aturan modul, backend, dan frontend.
3. [03-options-and-risks.md](03-options-and-risks.md) — eksplorasi opsi dan arah yang direkomendasikan.
4. [04-baseline-spec.md](04-baseline-spec.md) — requirement, non-scope, struktur target, command design, acceptance criteria, dan test plan.
5. [05-delivery-plan.md](05-delivery-plan.md) — task kecil yang dapat dieksekusi.
6. [06-vertical-slice-01.md](06-vertical-slice-01.md) — simulasi vertical slice pertama, tanpa implementasi.
7. [07-guide-plan-koreksi.md](07-guide-plan-koreksi.md) — urutan koreksi lintas temuan.
8. [decisions/README.md](decisions/README.md) — keputusan dan statusnya.
9. [08-mutation-authorization-matrix.md](08-mutation-authorization-matrix.md) — ownership dan denial evidence seluruh mutation berpermission.
10. [09-backup-signature-runbook.md](09-backup-signature-runbook.md) — provisioning, verification, dan rotation key lintas environment.

## Relevansi silang

- Temuan `[CR-*]` di dokumen 01 dipetakan ke aturan `[CTX-*]` di dokumen 02 dan task `[TASK-*]` di dokumen 05.
- Arah terpilih di dokumen 03 menjadi sumber requirement dokumen 04.
- Acceptance criteria dokumen 04 diturunkan menjadi acceptance criteria per task di dokumen 05.
- Slice 01 dokumen 06 awalnya menjadi runbook TASK-01/02 dan sekarang juga mencatat hasil eksekusinya.

## Batas audit

Audit bersifat repository-level sampling dan verifikasi otomatis. Ini bukan penetration test, audit kepatuhan, atau validasi proses bisnis ERP oleh subject-matter expert.
