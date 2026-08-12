# Paket Perbaikan Bug

Gunakan paket ini untuk pekerjaan `bug-fix`. Salin seluruh direktori ke koleksi work item yang sesuai dan ganti semua placeholder.

## Urutan Wajib

1. `01-BUG-REPORT.md` — perilaku aktual dan yang diharapkan, environment, reproduksi, tingkat keparahan, versi terdampak, dan bukti.
2. `02-ROOT-CAUSE-ANALYSIS.md` — rantai kegagalan, akar penyebab, faktor pendukung, escape analysis, dan blast radius.
3. `03-FIX-PLAN.md` — perbaikan aman minimum, alternatif, kompatibilitas, rollback, dan file terdampak.
4. `04-REGRESSION-TEST.md` — pengujian gagal sebelum perbaikan dan lulus setelah perbaikan, kasus terkait, dan gap pengujian sebelumnya.
5. `05-VERIFICATION-REPORT.md` — hasil reproduksi, pemeriksaan, efek samping, observasi deployment, dan penutupan.

Tambahkan juga `CONTEXT-PACK.md`, `DEVIATION-RECORD.md` bila diperlukan, serta `EVIDENCE-MANIFEST.md` sebelum pekerjaan berstatus selesai.
