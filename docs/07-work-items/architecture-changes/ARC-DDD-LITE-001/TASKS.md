# Task Breakdown — ARC-DDD-LITE-001

## Urutan

| Urutan | Task ID | Tujuan | Bergantung pada | Status |
|---:|---|---|---|---|
| 0 | `TSK-ARC-DDD-LITE-001-00` | Rekonsiliasi dokumen aktif dengan bukti kode | - | completed |
| 1 | `TSK-ARC-DDD-LITE-001-01` | Pulihkan konsistensi module tooling | persetujuan restore | completed |
| 2 | `TSK-ARC-DDD-LITE-001-02` | Migrasi pilot `HR/WorkLocations` melalui `REF-HR-WLOC-001` | task 01 verified | completed |
| 3 | `TSK-ARC-DDD-LITE-001-03` | Review hasil pilot dan susun dependency order | task 02 verified | ready |

## TSK-ARC-DDD-LITE-001-01 — Pulihkan Konsistensi Module Tooling

```yaml
status: completed
owner: unassigned
size: small
references:
  - ADR-0001
  - 01-DISCOVERY-RECORD.md
  - 04-IMPLEMENTATION-PLAN.md
```

### Tujuan

Membuat registrasi module command, generator test, dan artisan bootstrap kembali konsisten tanpa mengubah modul bisnis atau identifier database.

### Keputusan Pemilik

```yaml
gate: task-scope-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - pulihkan versi terakhir yang bekerja terlebih dahulu
  - jangan ubah struktur atau perilaku generator pada task ini
  - perubahan generator DDD-Lite menjadi task terpisah
evidence:
  - persetujuan eksplisit melalui percakapan
```

### Scope yang Diusulkan

- `app/Support/Modules/Commands/MakeModuleCommand.php`
- `app/Providers/ModuleServiceProvider.php` atau provider aktual yang meregistrasikan command
- test generator/module validation terkait
- dokumentasi/evidence task

### Dilarang

- migration atau ULID;
- pemindahan modul bisnis;
- deprecation `HR/IntegrationContracts`;
- perubahan route atau perilaku aplikasi.

### Kriteria Penerimaan

- [x] Keputusan restore versi terakhir yang bekerja disetujui.
- [x] File hasil restore identik dengan versi `HEAD` sebelum deletion working tree.
- [x] Artisan bootstrap berhasil.
- [x] Test generator terfokus lulus.
- [x] Module validation lulus.
- [x] Tidak ada perubahan perilaku generator, modul bisnis, migration, atau ULID.

### Perintah Verifikasi

```bash
git diff --exit-code HEAD -- app/Support/Modules/Commands/MakeModuleCommand.php
php artisan test --filter=MakeModuleCommandTest
php artisan module:validate
git diff --check
```

### Dokumentasi Pascakerja yang Disiapkan

- hasil aktual dicatat pada `05-VALIDATION-REPORT.md`;
- perintah, exit code, dan limitation dicatat pada `EVIDENCE-MANIFEST.md`;
- deviasi dicatat pada `DEVIATION-RECORD.md`;
- hasil task dicatat pada `06-COMPLETION-REPORT.md` tanpa menutup work item induk.

### Bukti Implementasi

- sumber: commit `f1f64b2661e081ff6f2bf7418ffd9795a0ff11bd`;
- Git blob sumber: `cd3c449747a14f0c286072a7a3374ba8b0ebbadf`;
- ukuran konten sumber: 11.468 byte;
- file dipulihkan: `app/Support/Modules/Commands/MakeModuleCommand.php`;
- perubahan isi tambahan: tidak ada;
- local patch context memastikan class `MakeModuleCommand` hadir setelah restore.

### Status Verifikasi

`VERIFIED`. File identik dengan `HEAD`; syntax PHP lulus; `MakeModuleCommandTest` lulus 4 test/23 assertion; `module:validate` menyatakan seluruh contract valid; `git diff --check` bersih.

## TSK-ARC-DDD-LITE-001-02 — Migrasi Pilot HR/WorkLocations

`REF-HR-WLOC-001` menjadi paket child kanonis untuk implementasi pilot ini. Pra-kerja, behavior baseline, scope file, acceptance, rollback, approval manusia, dan context task pertama telah tersedia pada `docs/07-work-items/refactorings/REF-HR-WLOC-001/`.

Status task induk: `completed`. Child `REF-HR-WLOC-001` telah terverifikasi: quality gate 527 test/3.260 assertion, regression consumer 41 test/215 assertion, build, route/module validation, authorization, invariance, review, dan baseline sync lulus.

## TSK-ARC-DDD-LITE-001-03 — Review Pilot dan Dependency Order

Status: `ready`, belum `in_progress`. Task ini hanya mengevaluasi bukti pilot serta graph import aktual untuk menentukan urutan slice berikutnya; tidak otomatis mengizinkan migrasi modul baru, perubahan generator, deprecation IntegrationContracts, atau ULID.
