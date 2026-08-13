# fitur paket

gunakan ini paket untuk `feature` pekerjaan. salin seluruh direktori ke dalam sesuai work item koleksi dan ganti semua placeholder.

## Urutan Wajib

1. `01-FEATURE-SPEC.md` — masalah, pengguna, scope, requirement, aturan bisnis, kriteria penerimaan, bukan tujuan dan dependensi.
2. `02-TECHNICAL-DESIGN.md` — Saat Ini status, diusulkan design, module/data/API/event/keamanan perubahan, alternatif dan risiko.
3. `03-IMPLEMENTATION-PLAN.md` — Ordered vertical slice, dependensi, diizinkan perubahan, rollout dan rollback.
4. `04-TEST-PLAN.md` — Unit, integrasi, kontrak, fitur, keamanan, edge-case dan regresi pengujian.
5. `05-REVIEW-REPORT.md` — Correctness, arsitektur, readability, keamanan, performa dan belum diselesaikan temuan.
6. `06-COMPLETION-REPORT.md` — hasil implementasi scope, deviasi, bukti, baseline sync dan tindak lanjut pekerjaan.

Gabungkan paket ini dengan seluruh file `templates/shared/`. `PLAN.md`, `TASKS.md`, `BACKLOG.md`, context, deviation, evidence, review, dan completion merupakan artefak inti; dokumen khusus dalam paket ini menggantikan template shared yang setara.
