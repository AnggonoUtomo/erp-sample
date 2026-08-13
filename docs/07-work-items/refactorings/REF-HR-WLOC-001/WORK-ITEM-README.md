---
id: DOC-REF-HR-WLOC-001-README
title: Identitas Work Item REF-HR-WLOC-001
document_type: work-item-readme
status: in_progress
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ARC-DDD-LITE-001, ADR-0001, FTR-ENG-001]
---

# REF-HR-WLOC-001 — Pilot DDD-Lite HR WorkLocations

ID: REF-HR-WLOC-001
Jenis: refactoring
Klasifikasi: CRITICAL
Status: in_progress
Owner: Pemilik proyek
Induk: ARC-DDD-LITE-001
Ditemukan oleh: null; work item ini merupakan paket implementasi TSK-ARC-DDD-LITE-001-02, bukan pekerjaan emergent.
Bergantung pada: FTR-ENG-001
Memblokir: TSK-ARC-DDD-LITE-001-03
ADR: ADR-0001
Boundary terdampak: Console dan HR
Modul terdampak: WorkLocations, Employees, EmployeeMovements, HRReports, AuditLogs, dan SystemSettings

## Tujuan

Menjalankan pilot struktur DDD-Lite minimal pada HR/WorkLocations sambil mempertahankan route, authorization, data, UI, audit, dan seluruh consumer yang ada.

## Scope dan Non-Scope

Scope mencakup pemindahan class/backend, route, provider, test milik modul, import consumer, serta adaptasi discovery route dan PHPUnit secara additive. Non-scope mencakup fitur, schema/ULID, public API/token auth, generator, IntegrationContracts, kontrak lintas modul, redesign coupling, dan frontend.

## Indeks Dokumen

| Dokumen | Fungsi | Status |
|---|---|---|
| 01-REFACTORING-PROPOSAL.md | keputusan scope dan acceptance | approved |
| 02-BEHAVIOR-BASELINE.md | perilaku sebelum perubahan | approved |
| 03-IMPLEMENTATION-PLAN.md | transformasi dan rollback | approved |
| 04-BEHAVIOR-VALIDATION.md | tempat bukti equivalence pascakerja | prepared |
| 05-COMPLETION-REPORT.md | laporan akhir | pending |
| PLAN.md | urutan work item | approved |
| TASKS.md | sumber status task | ready |
| BACKLOG.md | kandidat di luar scope | active |
| CONTEXT-PACK.md | konteks task pertama yang dipilih | ready |
| EVIDENCE-MANIFEST.md | bukti aktual | active |
| DEVIATION-RECORD.md | deviasi aktual | active |
| REVIEW-REPORT.md | review pra dan pasca implementasi | prepared |

## Keputusan Saat Ini dan Aksi Berikutnya

Work item telah memasuki implementasi. TSK-REF-HR-WLOC-001-01 adalah satu-satunya task in_progress; task lainnya tetap pending.

## Persetujuan dan Readiness

Gate refactoring-scope-approval disetujui Pemilik proyek pada 2026-08-14 berdasarkan konfirmasi eksplisit melalui interview keputusan teknis.

Putusan readiness: READY.
Disetujui oleh: Pemilik proyek.
Tanggal: 2026-08-14.
Catatan: seluruh keputusan blocking terselesaikan; baseline, allowlist, acceptance, verifikasi, rollback, dan context task pertama tersedia. READY tidak berarti coding telah dimulai.

## Definition of Ready

Feature/work-item readiness:

- [x] Masalah dan outcome eksplisit.
- [x] Scope dan non-scope didefinisikan.
- [x] Stakeholder dan pengguna terdampak diidentifikasi.
- [x] Requirement fungsional dan non-fungsional mempunyai ID.
- [x] Aturan serta edge case route binding, archive/restore, unique code, filtering, dan authorization tercatat dalam baseline.
- [x] Kriteria penerimaan dapat diuji.
- [x] Dependensi dan dampak modul diketahui.
- [x] Data/schema: no change; API: web/session tetap; security/authorization: semantics tetap; privacy: tidak ada data atau aliran baru.
- [x] ADR-0001 accepted dan keputusan policy disinkronkan.
- [x] Risiko/asumsi diselesaikan cukup untuk melanjutkan.
- [x] Rollout dan rollback didefinisikan.

Task readiness untuk TSK-REF-HR-WLOC-001-01:

- [x] Folder kanonis tersedia dan registry sinkron.
- [x] PLAN.md, TASKS.md, dan BACKLOG.md tersedia.
- [x] Tujuan task terbatas dan referensi approved tersedia.
- [x] Area diizinkan/dilarang serta expected files tercatat.
- [x] Acceptance, perintah verifikasi, dan test wajib tercatat.
- [x] Dependensi selesai; task dapat dikerjakan tanpa menciptakan requirement baru.
- [x] CONTEXT-PACK.md menunjuk task, scope, pola, risiko, dan verifikasi yang tepat.
