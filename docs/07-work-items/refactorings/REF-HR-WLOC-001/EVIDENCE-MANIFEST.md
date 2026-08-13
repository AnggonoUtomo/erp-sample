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
