---
id: PLAN-DDD-001
title: Rencana Implementasi DDD-Lite Berbasis Readiness
document_type: implementation-plan
status: active
version: 1.1.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002, DEP-HR-001, MIG-ID-001, FTR-ENG-001]
---

# Rencana Implementasi DDD-Lite Berbasis Readiness

## Prinsip Urutan

Tidak ada target “28 modul dalam 8 minggu” atau klaim production-ready tanpa estimasi dan bukti. Implementasi berjalan satu task aktif pada satu waktu dengan vertical slice terkecil yang mempertahankan perilaku.

## Tahap 0 — Integritas dan Persetujuan Dokumentasi

1. Rekonsiliasi fakta kode, ADR, katalog, dan dokumen historis.
2. Terima ADR-0001 sebagai struktur target tunggal.
3. Review ADR-0002 dan paket `DEP-HR-001`.
4. Pisahkan ULID ke `MIG-ID-001` dan pertahankan status `deferred`.
5. Pulihkan blocker working tree dan kumpulkan bukti verifikasi sebelum melanjutkan ke pilot.
6. Rekonsiliasi baseline engineering/testing melalui `FTR-ENG-001` sebelum menyusun pra-kerja pilot.

## Tahap 1 — Baseline Tooling yang Valid

Task implementasi pertama yang diusulkan adalah memulihkan konsistensi module tooling: `ModuleServiceProvider` tidak boleh meregistrasikan class yang hilang. Pilihan restore, replace, atau remove command membutuhkan konfirmasi atas penghapusan lokal `MakeModuleCommand.php`.

Kriteria minimum sebelum task dimulai:

- scope file disetujui;
- perilaku generator target mengikuti ADR-0001;
- test generator dan module validation dapat dijalankan;
- tidak ada ULID dalam scope;
- context pack task aktif tersedia.

## Tahap 2 — Modul Percontohan

Setelah tooling valid, pilih satu modul kecil dengan route, model, service, dan test yang representatif. Kandidat awal: `HR/WorkLocations`. Pemindahan hanya membuat folder target yang benar-benar diperlukan.

Gate per irisan:

1. baseline test terfokus dicatat;
2. file dan namespace dipindahkan;
3. provider/route/binding diperbarui;
4. route, permission, database behavior, dan response dibandingkan;
5. test terfokus, lint, serta architecture check lulus;
6. dokumentasi dan evidence manifest disinkronkan.

## Tahap 3 — Migrasi Berdasarkan Dependensi

Urutan modul berikutnya ditentukan setelah pilot, berdasarkan graph import aktual, bukan kategori “Console dulu” atau “HR dulu”. Modul dengan kontrak lintas batas mendapat compatibility plan tersendiri.

`HR/IntegrationContracts` tidak direstrukturisasi sebagai modul target. Ia mengikuti transisi `DEP-HR-001` setelah ADR-0002 diterima.

## Tahap Terpisah — Identifier ULID

`MIG-ID-001` tidak menjadi prerequisite struktur DDD-Lite. Work item tetap `deferred` sampai alasan bisnis, target schema, volume data, foreign-key mapping, compatibility window, rehearsal, backup, rollback, dan reconciliation disetujui.

## Status Readiness

| Area | Status | Alasan |
|---|---|---|
| Target struktur DDD-Lite | approved | ADR-0001 diterima |
| Baseline engineering/testing | completed | `FTR-ENG-001` selesai; current, target, deferred, dan rekomendasi telah dipisahkan |
| Katalog modul | evaluated | 27 target; dua kandidat rename; tanpa merge |
| Deprecation IntegrationContracts | approved, not ready | ADR-0002 accepted; compatibility dan removal readiness belum terpenuhi |
| Migrasi ULID | deferred | dipisahkan dan belum memiliki migration design |
| Coding restrukturisasi | not ready untuk slice berikutnya | baseline tooling dan engineering telah pulih; pilot `HR/WorkLocations` belum memiliki paket pra-kerja/readiness serta task aktif tersendiri |
