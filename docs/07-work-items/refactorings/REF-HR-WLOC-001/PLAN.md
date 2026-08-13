---
id: DOC-REF-HR-WLOC-001-PLAN
title: Rencana Kerja REF-HR-WLOC-001
document_type: work-plan
status: completed
version: 1.1.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ARC-DDD-LITE-001, ADR-0001]
---

# Rencana Kerja REF-HR-WLOC-001

## Outcome

HR/WorkLocations menggunakan struktur backend dan test minimal ADR-0001 dengan perilaku serta seluruh consumer tetap bekerja.

## Scope dan Non-Scope

Scope adalah route discovery kompatibel, pemindahan namespace/class WorkLocations, update consumer, colocation test, verifikasi, review, dan baseline sync. Non-scope adalah fitur, schema/ULID, authorization semantics, UI, generator, deprecation, dan redesign dependency.

## Urutan dan Dependensi

1. Persiapan dokumen, baseline, keputusan, dan readiness — selesai.
2. Tooling discovery route target/fallback — selesai.
3. Pemindahan class per concern dan cutover model atomik — selesai.
4. Pemindahan route serta test — selesai.
5. Validasi, review, completion, dan baseline sync — selesai.

FTR-ENG-001, task restore tooling induk, dan pilot ini telah selesai. `TSK-ARC-DDD-LITE-001-03` sekarang dapat menilai hasil pilot dan menyusun dependency order; task tersebut belum dimulai.

## Checkpoint

- Setiap task terfokus lulus sebelum task berikutnya.
- Namespace model dipindahkan atomik dan pencarian akhir tidak menemukan namespace lama.
- Root routes.php dihapus setelah target discovery terverifikasi.
- Acceptance matrix, review, evidence, dan baseline sync telah diisi dengan hasil aktual.

## Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| import consumer terlewat | inventaris rg, cutover atomik, full consumer regression |
| route ganda/hilang | target-first tanpa dual-load, unit test, snapshot route |
| test modul tidak ditemukan | suite PHPUnit additive dan eksekusi path eksplisit/full suite |
| perubahan authorization tak sengaja | policy hanya dipindah, permission/middleware dibandingkan |
| scope melebar ke coupling/generator | catat kandidat backlog dan hentikan bila mengubah keputusan |

## Rollout dan Rollback

Rollout adalah perubahan struktural dalam repository; tidak ada migration atau data rollout. Deploy mengikuti proses aplikasi yang ada setelah merge. Rollback dilakukan per task; cutover model dan semua import di-revert sebagai satu unit.

Release gate: work item tidak boleh disebut siap merge/deploy sebelum seluruh acceptance, focused/full regression, authorization allow/deny, module/route validation, quality check, build, review pascakerja, dan baseline sync lulus. Bila Gate/route/autoload gagal, rollback slice terkait sebelum rollout aplikasi.

## Persetujuan

Approved oleh Pemilik proyek pada 2026-08-14. Tidak ada pertanyaan teknis blocking yang tersisa.
