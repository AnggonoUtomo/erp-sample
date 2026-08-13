---
id: DOC-REF-HR-WLOC-001-VALIDATION
title: Validasi Perilaku Pilot DDD-Lite HR WorkLocations
document_type: behavior-validation
status: prepared
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [REF-WLOC-REQ-001, REF-WLOC-REQ-008]
---

# Validasi Perilaku Pilot DDD-Lite HR WorkLocations

Dokumen pascakerja ini sudah disiapkan tetapi belum berisi hasil implementasi. Status tidak boleh diubah menjadi verified sebelum perintah aktual dan hasilnya dicatat.

## Matriks Equivalence

| Area | Baseline sebelum | Bukti setelah | Status |
|---|---|---|---|
| enam route, verb, URI, name, action | snapshot route 2026-08-14 | snapshot route setelah perubahan | pending |
| middleware policy dan binding | inspeksi route/controller | inspeksi dan feature test | pending |
| akses tanpa permission | seluruh mutation mengembalikan 403 pada baseline | test denial setelah policy/provider dipindah | pending |
| CRUD, restore, force-delete | focused feature test lulus | focused test di lokasi modul | pending |
| filter, pagination, summary, map settings | focused feature test lulus | focused test dan response assertion | pending |
| validation dan normalization | request/DTO saat ini | focused test | pending |
| audit event dan module identifier | inspeksi service | focused test dan diff review | pending |
| schema, relasi, permission, navigation | inspeksi/diff baseline | no-change diff dan regression test | pending |
| consumer lintas modul | regression set 39/213 lulus | regression set setelah cutover | pending |
| discovery modul lama dan target | hanya routes.php saat baseline | unit test target-first/fallback | pending |
| discovery test | test berada di tests/Feature | test ditemukan di app/Modules | pending |
| frontend | path dan page props saat ini | no-change diff, typecheck/build | pending |

## Perintah Wajib Setelah Implementasi

    php artisan module:validate HR.WorkLocations --json
    php artisan route:list --path=hr/work-locations --json
    php artisan test app/Modules/HR/WorkLocations/Tests/Feature/HRWorkLocationTest.php
    php artisan test tests/Unit/ModuleContractValidatorTest.php tests/Unit/ModuleRegistryTest.php
    php artisan test tests/Feature/HREmployeeMovementTest.php tests/Feature/HREmployeeTest.php tests/Feature/HRIntegrationAssignmentSnapshotTest.php tests/Feature/HRReportCommandTest.php tests/Feature/HRReportHeadcountTest.php
    composer quality:check
    npm run build
    git diff --check

Pemeriksaan namespace lama, jumlah route, migration, permission, navigation, dan frontend harus ditambahkan ke EVIDENCE-MANIFEST.md dengan output aktual.

## Review Security dan Authorization Wajib

- Uji pengguna berizin untuk view, create, update, delete, restore, dan force-delete melalui flow HTTP.
- Uji pengguna tanpa permission menerima 403 untuk view dan seluruh mutation. Baseline sudah membuktikan denial seluruh mutation; assertion denial view ditambahkan sebagai characterization test tanpa mengubah perilaku aplikasi.
- Bandingkan permission key dan middleware string sebelum/sesudah.
- Buktikan Gate tetap memetakan model WorkLocation target ke WorkLocationPolicy target.
- Konfirmasi tidak ada perubahan authentication, session, role mapping, input validation, data exposure, secret, atau dependency.
- Tidak ada threat model baru yang diperlukan selama semantics dan trust boundary tidak berubah; setiap perubahan yang ditemukan menghentikan task dan memerlukan gate manusia baru.

## Aturan Penilaian

- Lulus hanya bila seluruh acceptance criterion mempunyai bukti.
- Kegagalan test tidak boleh disembunyikan dengan mengurangi coverage atau melemahkan assertion.
- Perbedaan route, authorization, data, audit, atau UI adalah deviasi perilaku dan menghentikan completion.
- Tidak ada klaim performa lebih baik; hanya absence of intentional regression yang dapat dinyatakan.

## Hasil Aktual

Belum tersedia karena coding belum dimulai.
