# Paket Deprecation dan Penghapusan

Gunakan paket ini untuk pekerjaan `deprecation-removal`. Salin seluruh direktori ke koleksi work item yang sesuai dan ganti semua placeholder.

## Urutan Wajib

1. `01-DEPRECATION-PROPOSAL.md` — antarmuka yang deprecated, alasan, consumer, pengganti, dan jangka waktu kompatibilitas.
2. `02-CONSUMER-IMPACT.md` — consumer yang diketahui, bukti penggunaan, upaya migrasi, dan pemilik.
3. `03-TRANSITION-PLAN.md` — pengumuman, dual-run/shim, langkah migrasi, telemetry, dan tenggat.
4. `04-REMOVAL-READINESS.md` — penggunaan sudah nol atau disetujui, backup, rollback, dokumentasi/pengujian, dan gate persetujuan.
5. `05-REMOVAL-REPORT.md` — artefak yang dihapus, status consumer, insiden, cleanup, dan release.

Tambahkan juga `CONTEXT-PACK.md`, `DEVIATION-RECORD.md` bila diperlukan, serta `EVIDENCE-MANIFEST.md` sebelum pekerjaan berstatus selesai.
