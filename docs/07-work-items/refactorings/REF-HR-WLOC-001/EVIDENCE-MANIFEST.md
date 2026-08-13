---
id: DOC-REF-HR-WLOC-001-EVIDENCE
title: Manifest Bukti REF-HR-WLOC-001
document_type: evidence-manifest
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Manifest Bukti REF-HR-WLOC-001

Work item: REF-HR-WLOC-001.
Commit atau PR: belum ada.
Perubahan saat ini: dokumentasi pra-kerja saja.
Migration: tidak dijalankan karena schema/data di luar scope.
Limitation: tidak ada baseline performa numerik yang disetujui; coding dan validasi setelah perubahan belum dilakukan.

## Bukti Pra-Implementasi

| Kriteria | Bukti | Hasil |
|---|---|---|
| repository valid sebelum perubahan | git status --short pada awal audit | bersih |
| kontrak modul baseline | php artisan module:validate HR.WorkLocations --json | exit 0, valid |
| route baseline | php artisan route:list --path=hr/work-locations --json | exit 0, enam route |
| perilaku terfokus dan consumer | `php artisan test tests/Feature/HRWorkLocationTest.php tests/Feature/HREmployeeMovementTest.php tests/Feature/HREmployeeTest.php tests/Feature/HRIntegrationAssignmentSnapshotTest.php tests/Feature/HRReportCommandTest.php tests/Feature/HRReportHeadcountTest.php` | exit 0, 39 test/213 assertion, 12,50 detik pada rerun final |
| consumer namespace terinventarisasi | rg pada app/, database/, tests/ | daftar dicatat di 02-BEHAVIOR-BASELINE.md dan TASKS.md |
| middleware dan permission baseline | inspeksi controller, policy, permissions.php, dan Support/Permissions.php | dicatat tanpa mengubah semantics |
| baseline authorization aktif | docs/04-design/AUTHORIZATION-MATRIX.md | WorkLocations disinkronkan dari kode; resource lain tetap belum direkonsiliasi |
| threat/security plan | proposal, 04-BEHAVIOR-VALIDATION.md, dan docs/08-quality/SECURITY-BASELINE.md | trust boundary tidak berubah; allow/deny, Gate, input, audit, dan no-change area diwajibkan |
| keputusan manusia | jawaban interview 2026-08-14 | seluruh keputusan blocking approved |

Rerun final tiga tahap—module validation, route snapshot, lalu 39 test—selesai exit 0 dalam 46,3 detik pada 2026-08-14. Durasi adalah fakta runner saat itu, bukan threshold performa.

## Pemeriksaan Gagal atau Dilewati

- php artisan module:validate --module=HR.WorkLocations --json gagal karena option --module tidak tersedia; perintah positional yang benar kemudian lulus.
- Perintah inspeksi awal memakai slug paket induk yang tidak ada; path kanonis ditemukan dengan `rg --files docs` dan inspeksi diulang berhasil.
- Copy-Item WorkLocationsService timeout tanpa membuat target; keadaan diverifikasi sebelum pemindahan dilanjutkan dengan apply_patch.
- Pint pertama pada cutover model menerima path sumber yang sudah dihapus dan gagal sebagai path tidak terbaca; rerun menyaring hanya file PHP yang ada.
- Characterization test Gate pertama gagal karena assertion membandingkan instance dengan class-string; output menunjukkan policy target benar dan assertion dikoreksi menjadi assertInstanceOf.
- Focused test provider pertama gagal 9/10 karena migration path relatif tidak lagi mencapai Database/Migrations; path disesuaikan satu level tanpa mengubah migration/schema lalu test diulang.
- apply_patch move route pertama ditolak karena hunk kosong; tidak ada perubahan parsial dan move diulang dengan hunk identitas.
- Test pasca-implementasi, composer quality:check, npm build, dan review kode belum dijalankan karena pekerjaan ini baru pra-kerja.

## Bukti Pascakerja

### TSK-REF-HR-WLOC-001-01

- RED: dua test baru gagal karena validator hanya menerima root routes.php dan runtime memilih legacy.
- GREEN: shared ModuleRegistry::routeFile memilih target terlebih dahulu lalu fallback; validator memakai resolver yang sama.
- `php artisan test tests/Unit/ModuleContractValidatorTest.php tests/Unit/ModuleRegistryTest.php`: exit 0, 5 test/6 assertion.
- `php artisan module:validate`: exit 0, seluruh contract valid.
- route snapshot WorkLocations: exit 0, enam route tetap.
- Pint terfokus dan git diff --check: exit 0.

Bukti task berikutnya ditambahkan secara incremental.

### TSK-REF-HR-WLOC-001-02

- WorkLocationData dipindahkan exact selain namespace ke Application/DTOs dan tiga import diperbarui.
- HRWorkLocationTest: exit 0, 9 test/27 assertion.
- module:validate HR.WorkLocations: exit 0, valid.
- Pencarian namespace DTO lama: nol hasil.
- Pint terfokus dan git diff --check: exit 0.

### TSK-REF-HR-WLOC-001-03

- WorkLocationsTransaction dipindahkan exact selain namespace ke Infrastructure/Transactions dan import service diperbarui.
- HRWorkLocationTest: exit 0, 9 test/27 assertion, 4,70 detik.
- module:validate HR.WorkLocations: exit 0, valid.
- Pencarian namespace transaction lama: nol hasil.
- Pint terfokus dan git diff --check: exit 0.
- Rangkaian command memakan sekitar 107 detik pada runner; tidak ada failure dan durasi bukan baseline performa aplikasi.

### TSK-REF-HR-WLOC-001-04

- WorkLocationsService dipindahkan exact selain namespace ke Application/Services dan import controller diperbarui.
- HRWorkLocationTest: exit 0, 9 test/27 assertion, 20,34 detik.
- module:validate HR.WorkLocations: exit 0, valid.
- Pencarian namespace service lama: nol hasil.
- Pint terfokus dan git diff --check: exit 0.

### TSK-REF-HR-WLOC-001-05

- WorkLocation dipindahkan exact selain namespace ke Infrastructure/Models; 18 consumer produksi/test diperbarui atomik tanpa alias.
- Pencarian namespace model lama pada app/, database/, dan tests/: nol hasil.
- Regression set: exit 0, 39 test/213 assertion, 6,85 detik.
- module:validate HR.WorkLocations: exit 0, valid.
- Route snapshot: enam route tetap; middleware sama dan FQCN model menunjuk lokasi target.
- Setelah formatter mekanis pada service, regression set diulang: 39 test/213 assertion lulus dalam 7,87 detik; Pint lulus.

### TSK-REF-HR-WLOC-001-06

- Dua FormRequest dipindahkan exact selain namespace ke Presentation/Http/Requests; import controller diperbarui.
- HRWorkLocationTest: exit 0, 9 test/27 assertion, 2,41 detik.
- module:validate HR.WorkLocations: exit 0, valid.
- Pencarian namespace request lama: nol hasil; Pint terfokus dan git diff --check lulus.

### TSK-REF-HR-WLOC-001-07

- WorkLocationPolicy dipindahkan exact selain namespace ke Presentation/Policies; import provider diperbarui.
- Characterization test Gate ditambahkan; focused suite lulus 10 test/28 assertion dalam 2,51 detik.
- Existing allow/deny HTTP test tetap lulus; module validation valid; namespace policy lama nol; Pint dan diff check lulus.

### TSK-REF-HR-WLOC-001-08

- WorkLocationsController dipindahkan exact selain namespace ke Presentation/Http/Controllers; import route diperbarui.
- Focused suite lulus 10 test/28 assertion dalam 2,23 detik.
- Route snapshot tetap enam route dengan verb, URI, name, middleware, dan binding sama; action FQCN menunjuk controller target.
- module validation valid; namespace controller lama nol; Pint dan diff check lulus.

### TSK-REF-HR-WLOC-001-09

- Provider dipindahkan ke Infrastructure/Providers dan module.php menunjuk namespace target.
- First run menangkap migration path relatif salah; path dikoreksi ke migration yang sama tanpa perubahan schema.
- Focused suite rerun lulus 10 test/28 assertion dalam 2,03 detik; Gate mapping dan allow/deny tetap lulus.
- module validation valid; namespace provider lama nol; Pint dan diff check lulus.

### TSK-REF-HR-WLOC-001-10

- Root routes.php dipindahkan ke Presentation/Routes/web.php; file legacy tidak tersisa.
- Focused WorkLocations dan unit tooling lulus 15 test/34 assertion dalam 3,03 detik.
- module validation valid; route snapshot tepat enam route dengan verb, URI, name, middleware, dan binding ekuivalen.
- Pint route dan git diff --check lulus.

### TSK-REF-HR-WLOC-001-11

- HRWorkLocationTest dipindahkan ke module-local Tests/Feature dengan namespace App yang sesuai PSR-4.
- PHPUnit menambah suite Module secara additive; Unit dan Feature tetap.
- Characterization denial index tanpa permission ditambahkan; tidak ada assertion lama yang dikurangi.
- Path langsung dan suite Module masing-masing lulus 11 test/29 assertion dalam 2,46 dan 2,89 detik.
- `composer dump-autoload --strict-psr`: exit 0, 7.404 class; Pint dan diff check lulus.
