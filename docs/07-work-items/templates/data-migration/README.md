# Paket Migrasi Data

Gunakan paket ini untuk pekerjaan `data-migration`. Salin seluruh direktori ke koleksi work item yang sesuai dan ganti semua placeholder.

## Urutan Wajib

1. `01-MIGRATION-PROPOSAL.md` — alasan bisnis, sumber/target, volume, downtime, risiko, dan ownership.
2. `02-SOURCE-TARGET-MAPPING.md` — pemetaan field/entity, transformasi, default, record ditolak, dan lineage.
3. `03-DATA-QUALITY-ASSESSMENT.md` — kelengkapan, validitas, duplikasi, integritas referensial, dan remediation.
4. `04-MIGRATION-PLAN.md` — fase, rehearsal, backup, batching, idempotency, freeze window, dan cutover.
5. `05-ROLLBACK-PLAN.md` — pemicu, jalur restore, reverse transform, batas waktu, dan batas kehilangan data.
6. `06-RECONCILIATION-REPORT.md` — jumlah data, total, hash/sample, exception, dan sign-off.
7. `07-POST-MIGRATION-REPORT.md` — hasil, masalah residual, monitoring, cleanup, dan pelajaran.

Tambahkan juga `CONTEXT-PACK.md`, `DEVIATION-RECORD.md` bila diperlukan, serta `EVIDENCE-MANIFEST.md` sebelum pekerjaan berstatus selesai.
