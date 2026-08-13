# 01 Catatan Penemuan

## Metadata

```yaml
work_item: ARC-DDD-LITE-001
status: reviewed
owner: unassigned
last_updated: 2026-08-13
```

## Fakta Repository

1. Terdapat 28 manifest: 11 Console, 16 HR, dan 1 DocumentManagement.
2. Struktur modul masih dominan datar. Migration sudah module-local; route berada pada `routes.php`; 101 test Feature dan 5 test Unit berada pada root test suite.
3. Tidak ditemukan test pada `app/Modules/**/Tests/`.
4. `HR/IntegrationContracts` mempunyai 23 file PHP, 4 snapshot contract, DTO/event envelope, registry, privacy guard, 3 command, dan 4 Eloquent provider. Ia tidak mempunyai table, route, navigation, atau use case mutation.
5. Pencarian namespace `HR\IntegrationContracts` di luar modul hanya menemukan test sebagai consumer langsung. Tidak ada production consumer langsung yang ditemukan.
6. Modul bisnis sudah memiliki integration surface aktif pada `EmployeeContracts`, `EmployeeDocuments`, `EmployeeMovements`, `Employees`, dan `Offboardings`.
7. `DocumentManagement/Foundation` memiliki route dan empat tabel DMS; description “contract-only foundation” tidak sesuai perilaku aktual.
8. Semua migration primary/foreign key utama yang diperiksa memakai `$table->id()` dan `foreignId()`, bukan ULID.
9. Pada saat discovery, `MakeModuleCommand.php` hilang dari working tree, tetapi `ModuleServiceProvider` masih mengimpor dan meregistrasikannya. File kemudian dipulihkan exact melalui `TSK-ARC-DDD-LITE-001-01`; test generator dan validasi modul pascarestore lulus.
10. Branch `dev` menghapus 196 file dokumentasi dibanding `main` commit `aab3c87a88ccdda64c95051ec72b431648e0ecdf`. Keputusan accepted lama tetap penting sebagai bukti asal perilaku.

## Konflik Dokumen yang Ditemukan

- Jumlah modul pernah ditulis 18 meskipun kode mempunyai 28 manifest.
- Struktur target berbeda antara `Infrastructure/Persistence/Models` dan `Infrastructure/Models`, antara root `Routes` dan `Presentation/Routes`, serta antara folder lengkap wajib dan struktur minimal.
- Dokumen database mengklaim ULID `CHAR(26)` meskipun migration memakai bigint.
- API spec mengklaim endpoint `/api/v1` IntegrationContracts yang tidak ada pada route.
- Dokumen mengklaim generator/test/validation bekerja tanpa bukti pada working tree sekarang.

## Hasil Evaluasi Modul

- 27 modul target dipertahankan sebagai kapabilitas mandiri bila ADR-0002/DEP-HR-001 diterima.
- Tidak ada merge yang diusulkan.
- Kandidat rename: `Departements` menjadi `Departments`; `DocumentManagement/Foundation` menjadi `DocumentManagement/Documents`.
- `HR/IntegrationContracts` menjadi kandidat deprecation sebagai shell teknis.

Detail berada pada `docs/03-architecture/MODULE-CATALOG.md`.

## Pertanyaan/Gate Terbuka

1. Approval snapshot historis `BL-2026-001-pre-seos`.
2. Pra-kerja dan approval task adaptasi generator ke struktur minimal ADR-0001.

## Keterlacakan

- ADR-0001
- ADR-0002
- DEP-HR-001
- MIG-ID-001
- BL-2026-001-pre-seos
