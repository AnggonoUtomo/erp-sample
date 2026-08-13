# dependensi Upgrade paket

gunakan ini paket untuk `dependency-upgrade` pekerjaan. salin seluruh direktori ke dalam sesuai work item koleksi dan ganti semua placeholder.

## Urutan Wajib

1. `01-UPGRADE-PROPOSAL.md` — dependensi, saat ini/target versi, alasan, dukung/keamanan/license status.
2. `02-COMPATIBILITY-ASSESSMENT.md` — breaking perubahan, transitive dependensi, platform/runtime dan API dampak.
3. `03-UPGRADE-PLAN.md` — Incremental langkah, lockfile/config perubahan, pengujian matriks dan rollback.
4. `04-VALIDATION-REPORT.md` — Build, pengujian, keamanan scan, performa dan runtime observation.
5. `05-COMPLETION-REPORT.md` — versi adopted, perubahan, exceptions, tindak lanjut dan registry perbarui.

Gabungkan paket ini dengan seluruh file `templates/shared/`. `PLAN.md`, `TASKS.md`, `BACKLOG.md`, context, deviation, evidence, review, dan completion merupakan artefak inti; dokumen khusus dalam paket ini menggantikan template shared yang setara.
