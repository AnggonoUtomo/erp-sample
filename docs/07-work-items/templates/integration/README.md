# Paket Integrasi

Gunakan paket ini untuk pekerjaan `integration`. Salin seluruh direktori ke koleksi work item yang sesuai dan ganti semua placeholder.

## Urutan Wajib

1. `01-INTEGRATION-BRIEF.md` — provider, use case, ownership, environment, klasifikasi data, biaya, dan batasan.
2. `02-CONTRACT-SPEC.md` — endpoint/message, authentication, schema, versioning, idempotency, dan kompatibilitas.
3. `03-FAILURE-MODES.md` — timeout, retry, rate limit, kegagalan parsial, duplikasi, ordering, fallback, dan circuit breaking.
4. `04-SECURITY-ASSESSMENT.md` — secret, trust boundary, validasi webhook, privasi, skenario ancaman, dan kontrol.
5. `05-IMPLEMENTATION-PLAN.md` — adapter boundary, sandbox, observability, rollout, dan rollback.
6. `06-SANDBOX-TEST-REPORT.md` — test case, response provider, jalur error, rate limit, dan bukti.
7. `07-PRODUCTION-READINESS.md` — credential, limit, alert, runbook, kontak dukungan, biaya, rollback, dan persetujuan.

Gabungkan paket ini dengan seluruh file `templates/shared/`. `PLAN.md`, `TASKS.md`, `BACKLOG.md`, context, deviation, evidence, review, dan completion merupakan artefak inti; dokumen khusus dalam paket ini menggantikan template shared yang setara.
