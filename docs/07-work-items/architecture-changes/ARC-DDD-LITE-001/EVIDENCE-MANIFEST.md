# Manifest Bukti

```yaml
work_item: ARC-DDD-LITE-001
commit_or_pr: null
files_changed:
  - app/Support/Modules/Commands/MakeModuleCommand.php
  - dokumentasi ARC-DDD-LITE-001
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
security_checks: []
migrations: []
documentation_inspected:
  - governance SEOS wajib
  - ADR dan katalog arsitektur
  - work item ARC-DDD-LITE-001
  - dokumen historis pada main commit
known_limitations:
  - full test suite tidak dijalankan; scope restore exact diverifikasi dengan test terfokus
  - route:list tidak termasuk scope task restore
  - tidak ada full test pass pada working tree saat ini
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

## Pemeriksaan Gagal/Dilewati

| Pemeriksaan | Alasan |
|---|---|
| Percobaan verifikasi gabungan pertama | runner terminal timeout tanpa output; kemudian digantikan pemeriksaan individual yang lulus |
| Full backend test | tidak dijalankan; restore tidak menghasilkan diff kode terhadap HEAD |
| Runtime `route:list` | tidak termasuk scope task restore |
| Frontend lint/typecheck | hasil sebelumnya timeout |
