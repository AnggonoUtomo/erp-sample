# 01 — Code Review and Quality

## Ringkasan

Fondasi modular sudah nyata dan sebagian besar operasi utama mempunyai feature test. Namun baseline belum hijau: `php artisan test` menghasilkan **159 lulus, 3 gagal (427 assertions)**; `npm run build` lulus; `npm run format:check` gagal pada **96 file**. Karena quality gate gagal, baseline belum layak dijadikan template “known good”.

## Temuan berurutan

### CR-01 — Critical: test saling merusak filesystem global

- Bukti: `SharedKernelTest::test_module_manifest_can_register_domain_event_listeners` dan `MakeModuleCommandTest` sama-sama membuat/menghapus `app/Modules/TmpProject`.
- Dampak: hasil suite bergantung urutan/timing; penghapusan parent oleh satu test menyebabkan `DirectoryNotFoundException`/`mkdir(): File exists` pada test lain. Ini menjelaskan dua kegagalan yang teramati dan menghambat parallel test.
- Koreksi: gunakan direktori unik per test/run, inject module root ke registry/generator, dan cleanup hanya direktori milik test.
- Terkait: [CTX-04](02-context-and-architecture.md#ctx-04--aturan-test), [TASK-01](05-delivery-plan.md#task-01--isolasi-filesystem-test).

### CR-02 — Required: test media tidak memiliki lifecycle storage yang stabil

- Bukti: penghapusan avatar gagal di `FilesystemIterator` untuk `storage/framework/testing/disks/public/1`; test menggunakan `Storage::fake('public')` bersama Media Library.
- Dampak: regresi avatar tidak dapat dibedakan dari kegagalan fixture/storage cleanup.
- Koreksi: buat setup Media Library disk yang eksplisit, fixture per test, dan assertion file/database sebelum/sesudah delete.
- Terkait: [TASK-02](05-delivery-plan.md#task-02--stabilkan-test-avatar).

### CR-03 — Required/Security: full restore menjalankan SQL tidak tepercaya

- Bukti: `BackupRestoreService.php:463` menjalankan statement hasil file restore melalui `unprepared()`; restore ZIP menulis entry ke storage di `:606`.
- Kontrol yang ada: permission khusus, confirmation text, pemeriksaan tipe/manifest, dan invalidasi session.
- Risiko tersisa: fitur ini secara desain memberi kemampuan eksekusi SQL dan overwrite data kepada pemegang permission; parser SQL berbasis statement perlu dibuktikan aman terhadap dialect/transaction/error parsial. ZIP extraction harus mempertahankan jaminan anti path traversal, symlink, ukuran/decompression bomb, dan batas jumlah entry.
- Koreksi: threat model, allowlist asal backup dan format/version, checksum/signature, batas ukuran/entry, staging + dry-run, backup pra-restore, transaksi/rollback yang terdokumentasi, audit event, serta test malicious archive/partial SQL.
- Terkait: [ADR-003](decisions/003-risk-first-remediation.md), [TASK-07](05-delivery-plan.md#task-07--hardening-full-restore).

### CR-04 — Required: format gate tidak hijau dan lint bersifat mutating

- Bukti: `npm run format:check` menemukan 96 file; script `lint` memakai `eslint . --fix`, sehingga tidak cocok sebagai read-only CI gate.
- Dampak: style drift besar, review noisy, CI sulit membedakan check dari rewrite.
- Koreksi: tambah `lint:check` non-mutating, pertahankan `lint`/`lint:fix` untuk lokal, normalisasi formatting dalam perubahan terpisah.
- Terkait: [TASK-04](05-delivery-plan.md#task-04--quality-gates-non-mutating).

### CR-05 — Required: drift path frontend dan aturan acronym

- Bukti: terdapat `resources/js/pages/hr/h-r-reference-data/index.tsx` di samping path canonical `hr/hr-reference-data`; guide menyatakan `HR` tidak boleh menjadi `h-r`.
- Dampak: dead/duplicate page, kebingungan generator, dan risiko resolver memuat artefak salah.
- Koreksi: buktikan referensi, hapus/migrasikan melalui task tersendiri, lalu tambah regression test generator acronym.
- Terkait: [CTX-03](02-context-and-architecture.md#ctx-03--aturan-frontend), [TASK-03](05-delivery-plan.md#task-03--hilangkan-drift-acronym).

### CR-06 — Required: module contract tidak sepenuhnya konsisten

- Bukti: `app/Modules/Console/ActivityCenters` tidak memiliki `navigation.php`, sementara guide menyebut contract wajib; beberapa contoh dokumentasi juga berbeda kapitalisasi path dari implementasi aktual (`app/Modules/HR/...`).
- Dampak: developer/generator tidak tahu apakah navigation wajib atau opsional.
- Koreksi: tetapkan schema contract formal dan validator command; nyatakan export boleh false dan kapan file boleh absen.
- Terkait: [ADR-002](decisions/002-contract-as-source-of-truth.md), [TASK-05](05-delivery-plan.md#task-05--validator-contract-modul).

### CR-07 — Maintainability: service dan page composer terlalu besar

- Bukti: `SystemSettingService.php` 958 baris, `BackupRestoreService.php` 523 baris; beberapa page/panel 300–397 baris.
- Dampak: terlalu banyak tanggung jawab, review/security reasoning sulit, perubahan mudah saling mengganggu.
- Koreksi: pisahkan orchestration dari adapter/validator/serializer berdasarkan use case, bukan helper generik; lakukan setelah test baseline stabil.
- Terkait: [TASK-08](05-delivery-plan.md#task-08--dekomposisi-service-berisiko).

### CR-08 — Test coverage: lebar tetapi belum menutup negative/security paths

Feature CRUD utama tersedia, tetapi evidence yang kurang mencakup unauthorized matrix per mutasi, module dependency invalid/cycle, failure rollback restore, archive traversal/bomb, queue failure/retry, dan test frontend komponen/interaksi. `tests/Unit/ExampleTest.php` masih placeholder.

## Hal positif

- Authorization umumnya diletakkan di policy/FormRequest/controller dan route module berada di bawah auth.
- Build produksi berhasil.
- Feature tests mencakup CRUD, soft delete/restore/force delete untuk banyak master HR.
- Shared Kernel dan Integration Layer mempunyai unit test khusus.

## Keputusan dokumentasi tahap ini

Baseline dinyatakan **Needs Correction**, bukan rejected. Prioritas pertama adalah determinisme test, kemudian contract/quality gates, lalu hardening fitur restore. Lihat [ADR-001](decisions/001-documentation-only-baseline.md).

