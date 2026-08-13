# Manifest Bukti

```yaml
work_item: ARC-DDD-LITE-001
commit_or_pr: [43fe02a, b22302d..0ad01d6]
files_changed:
  - app/Support/Modules/Commands/MakeModuleCommand.php
  - app/Support/Modules/ModuleRegistry.php dan ModuleContractValidator.php
  - app/Modules/HR/WorkLocations serta consumer namespace yang tercatat pada child
  - phpunit.xml dan test tooling/consumer yang tercatat pada child
  - dokumentasi ARC-DDD-LITE-001
  - dokumentasi REF-HR-WLOC-001 dan baseline terdampak
commands_run:
  - inventaris file module.php
  - pencarian migration Schema::create dan table id
  - pencarian import IntegrationContracts
  - perbandingan dokumen main dan dev
  - "git diff --exit-code HEAD -- app/Support/Modules/Commands/MakeModuleCommand.php; php artisan test --filter=MakeModuleCommandTest; php artisan module:validate; git diff --check"
tests:
  - "php artisan test --filter=MakeModuleCommandTest: gagal, class command tidak ada"
  - "percobaan pertama setelah restore: runner timeout 120 detik tanpa output; tidak diklaim lulus"
  - "setelah runner pulih: MakeModuleCommandTest lulus 4 test, 23 assertion, exit 0"
  - "setelah runner pulih: module:validate menyatakan seluruh contract valid, exit 0"
static_analysis:
  - "php -l MakeModuleCommand.php: tidak ada syntax error, exit 0"
  - "git diff --check: bersih, exit 0"
security_checks:
  - "pilot WorkLocations: Gate mapping serta allow/deny lulus; permission/auth/session tidak berubah"
migrations: []
documentation_inspected:
  - governance SEOS wajib
  - ADR dan katalog arsitektur
  - work item ARC-DDD-LITE-001
  - dokumen historis pada main commit
  - paket pra-kerja `REF-HR-WLOC-001`
  - ADR-0001 setelah sinkronisasi keputusan lokasi policy framework
known_limitations:
  - full test suite tidak dijalankan pada task restore exact; kemudian dijalankan dan lulus pada child pilot
  - route:list tidak termasuk scope task restore; kemudian diverifikasi pada child pilot
  - CI remote, deployment, dan browser E2E tidak dijalankan oleh pilot
```

## Bukti Kriteria Discovery

| Kriteria | Bukti | Hasil |
|---|---|---|
| Jumlah modul aktual | 28 `module.php` | terverifikasi |
| Struktur target tunggal | ADR-0001 | accepted |
| Consumer shell integration | search PHP import | hanya test di luar modul |
| Identifier aktual | migration `$table->id()`/`foreignId()` | bigint, bukan ULID |
| Dokumen lama tetap terlacak | main commit + baseline snapshot | diusulkan |
| Provenance restore MakeModuleCommand | commit `f1f64b2661e081ff6f2bf7418ffd9795a0ff11bd`, blob `cd3c449747a14f0c286072a7a3374ba8b0ebbadf`, 11.468 byte | diterapkan |
| Pilot WorkLocations | child `REF-HR-WLOC-001`, baseline 39 test/213 assertion, hasil 41 test/215 assertion dan full quality 527/3.260 | completed dan verified |

## Bukti Pilot WorkLocations 2026-08-14

- commit pra-kerja `43fe02a`; implementasi incremental `b22302d` sampai `0ad01d6`;
- struktur target minimal tanpa Domain/Integration kosong;
- enam route ekuivalen dan kontrak seluruh modul valid;
- Gate mapping serta authorization allow/deny lulus;
- namespace lama nol; invariant migration, permission/navigation, frontend, dan dependency manifest tidak berubah;
- build 2.204 module dan strict PSR 7.404 class lulus;
- detail lengkap pada evidence child `REF-HR-WLOC-001`.

## Pemeriksaan Gagal/Dilewati

| Pemeriksaan | Alasan |
|---|---|
| Percobaan verifikasi gabungan pertama | runner terminal timeout tanpa output; kemudian digantikan pemeriksaan individual yang lulus |
| Full backend test | tidak dijalankan; restore tidak menghasilkan diff kode terhadap HEAD |
| Runtime `route:list` | tidak termasuk scope task restore |
| Frontend lint/typecheck | hasil sebelumnya timeout |
