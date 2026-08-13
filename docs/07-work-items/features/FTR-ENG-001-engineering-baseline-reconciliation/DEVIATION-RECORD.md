---
id: DEV-FTR-ENG-001
title: Catatan Deviasi Rekonsiliasi Baseline Engineering
document_type: deviation-record
status: accepted
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: FTR-ENG-001
related: [CAND-TEST-001, EVD-FTR-ENG-001]
---

# Catatan Deviasi — FTR-ENG-001

    work_item: FTR-ENG-001
    status: accepted
    requires_adr: false

## Deviasi Verifikasi

Rencana menggunakan npm run quality:check sebagai pemeriksaan agregat. Command tersebut melewati timeout runner sekitar lima menit. Pemisahan command kemudian dilakukan:

- lint, format check, typecheck, frontend test, dan build dijalankan sebagai command terpisah;
- percobaan paralel menimbulkan tiga timeout worker Vitest;
- rerun Vitest terisolasi lulus 12 file/28 test;
- hasil diperlakukan sebagai limitation runner, bukan assertion failure aplikasi.

Deviasi tidak mengubah scope, baseline keputusan, code, config, test, dependency, atau workflow. Stabilisasi runner dicatat sebagai CAND-TEST-001.
