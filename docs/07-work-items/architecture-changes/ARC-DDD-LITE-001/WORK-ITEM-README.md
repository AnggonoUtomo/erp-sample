# ARC-DDD-LITE-001 — Restrukturisasi Modul ke DDD-Lite Adaptif

```yaml
id: ARC-DDD-LITE-001
kind: architecture-change
classification: CRITICAL
status: in_progress
owner: unassigned
created_at: 2026-08-12
updated_at: 2026-08-13
parent: null
discovered_by: null
depends_on: []
blocks: []
related_adrs: [ADR-0001, ADR-0002]
affected_boundaries: [Console, HR, DocumentManagement]
affected_modules: [all]
```

## Tujuan

Menjadikan ADR-0001 sebagai struktur target utama sekarang, memigrasikan modul secara incremental tanpa perubahan perilaku, serta memulihkan keterlacakan keputusan historis ke dalam baseline SEOS aktif.

## Scope

- Struktur DDD-Lite baku tetapi minimal sesuai kebutuhan.
- Evaluasi seluruh katalog modul berdasarkan tanggung jawab bisnis aktual.
- Pemindahan test, route, class, dan namespace secara bertahap per module slice.
- Compatibility check untuk contract/event lintas modul.
- Sinkronisasi dokumentasi aktif berdasarkan bukti kode.

## Non-Scope

- Migrasi primary key ke ULID; dipisahkan ke `MIG-ID-001`.
- Fitur baru atau perubahan perilaku bisnis.
- Public API, webhook, broker, outbox, atau microservice.
- Penghapusan langsung `HR/IntegrationContracts`; dikelola oleh `DEP-HR-001`.

## Status Gate

```yaml
gate: architecture-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - perilaku aplikasi dipertahankan
  - lokasi baku menggunakan struktur minimal
  - perubahan kontrak dan ULID mempunyai work item terpisah
evidence:
  - ADR-0001
  - konfirmasi eksplisit melalui interview evaluasi
```

Task restore module tooling telah selesai dan terverifikasi. Work item tetap `in_progress`; hal ini tidak berarti restrukturisasi seluruh modul sudah selesai.

## Dokumen

| Dokumen | Status |
|---|---|
| `01-DISCOVERY-RECORD.md` | updated |
| `02-BOUNDARY-PROPOSAL.md` | approved via ADR-0001 dan ADR-0002 |
| `03-IMPACT-ASSESSMENT.md` | updated |
| `04-IMPLEMENTATION-PLAN.md` | updated |
| `05-VALIDATION-REPORT.md` | criteria prepared; execution pending |
| `06-COMPLETION-REPORT.md` | not started |
| `TASKS.md` | task restore completed; tidak ada task coding aktif |
| `CONTEXT-PACK.md` | documentation context current |
| `EVIDENCE-MANIFEST.md` | discovery evidence recorded |
| `DEVIATION-RECORD.md` | lima deviasi tercatat; gangguan runner telah resolved |
