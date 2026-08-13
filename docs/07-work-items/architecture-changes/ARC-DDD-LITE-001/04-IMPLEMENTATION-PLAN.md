# 04 Rencana Implementasi

## Metadata

```yaml
work_item: ARC-DDD-LITE-001
status: proposed
owner: unassigned
last_updated: 2026-08-13
```

## Urutan

1. **Integritas baseline tooling** — selesaikan class generator yang hilang; tidak mengubah struktur modul lain.
2. **Pilot `HR/WorkLocations`** — pindahkan satu vertical slice ke lokasi ADR-0001 dan buktikan perilaku route/permission/data tetap.
3. **Review hasil pilot** — koreksi aturan/generator bila bukti menunjukkan gap.
4. **Migrasi per dependency graph** — satu modul aktif setiap saat.
5. **Transisi integration shell** — hanya melalui `DEP-HR-001` setelah ADR-0002 accepted.
6. **Baseline sync** — katalog dan bukti diperbarui setelah setiap slice verified.

## Aturan Slice

- Tidak membuat semua folder target sekaligus.
- Tidak mengubah database schema atau identifier.
- Tidak mengganti semantics service/contract.
- Tidak mencampur rename kandidat dengan pemindahan struktur kecuali disetujui sebagai work item tersendiri.
- Repository harus kembali ke keadaan valid pada akhir setiap task.

## Rollback

Setiap task memuat daftar file sebelum/sesudah, test baseline, dan commit/referensi yang dapat direvert. Rollback hanya terhadap slice task, bukan seluruh repository. Migration database tidak berada dalam scope.

## Task Pertama yang Dipilih

`TSK-ARC-DDD-LITE-001-01 — Pulihkan konsistensi module tooling` telah selesai. File dipulihkan exact dari `HEAD`; test generator, module validation, syntax check, dan diff check lulus. Adaptasi generator ke ADR-0001 tetap menjadi task terpisah dan belum aktif.
