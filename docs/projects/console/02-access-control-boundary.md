# 02 — Access Control Protected Authorization Boundary

Tanggal telusur: 2026-07-19  
Status: selesai untuk baseline dokumentasi Task 02

Dokumen ini mencatat hasil telusur module `Console/AccessControls` seolah-olah module ini baru akan dibangun. Tujuannya bukan rewrite, tetapi memastikan boundary role/permission, visibility role protected, dan denial matrix sudah bisa dipahami serta diuji ulang.

## Ringkasan hasil

Access Control adalah boundary administrasi role dan permission aplikasi. Module ini sudah punya empat lapisan perlindungan:

1. route wajib `auth`;
2. controller middleware memakai policy/gate;
3. request validation menolak payload berbahaya seperti role `super-system`;
4. service read model menyembunyikan role protected dari actor non-super-system.

Frontend membantu UX dengan disabled state dan protected badge, tetapi bukan security boundary utama. Keamanan tetap harus dianggap berada di backend.

## File sumber yang ditelusuri

Backend:

- `app/Modules/Console/AccessControls/module.php`
- `app/Modules/Console/AccessControls/routes.php`
- `app/Modules/Console/AccessControls/permissions.php`
- `app/Modules/Console/AccessControls/navigation.php`
- `app/Modules/Console/AccessControls/Providers/AccessControlsServiceProvider.php`
- `app/Modules/Console/AccessControls/Policies/AccessControlPolicy.php`
- `app/Modules/Console/AccessControls/Http/Controllers/AccessControlController.php`
- `app/Modules/Console/AccessControls/Http/Requests/StoreRoleRequest.php`
- `app/Modules/Console/AccessControls/Http/Requests/UpdateRoleRequest.php`
- `app/Modules/Console/AccessControls/Http/Requests/SyncRolePermissionsRequest.php`
- `app/Modules/Console/AccessControls/Http/Requests/StorePermissionRequest.php`
- `app/Modules/Console/AccessControls/Services/AccessControlService.php`
- `app/Modules/Console/AccessControls/DTO/*`
- `app/Modules/Console/AccessControls/Transactions/AccessControlTransaction.php`

Frontend:

- `resources/js/pages/console/access-control/index.tsx`
- `resources/js/pages/console/access-control/types.ts`
- `resources/js/pages/console/access-control/access-control-components/role-permission-workspace.tsx`
- `resources/js/pages/console/access-control/access-control-components/role-control-card.tsx`
- `resources/js/pages/console/access-control/access-control-components/permission-module-panel.tsx`
- `resources/js/pages/console/access-control/access-control-components/add-role-dialog.tsx`
- `resources/js/pages/console/access-control/access-control-components/delete-role-dialog.tsx`

Test:

- `tests/Feature/AccessControlTest.php`
- `tests/Feature/MutationRouteAuthorizationTest.php`
- `tests/Unit/ModulePermissionRegistryTest.php`

## Contract module

`module.php` mendefinisikan module:

- project: `Console`;
- title: `Kontrol Akses`;
- slug: `access-controls`;
- provider: `AccessControlsServiceProvider`;
- exports: `routes`, `permissions`, `navigation`;
- dependencies: kosong.

Artinya Access Control berdiri sebagai module Console foundational. Module lain boleh mendefinisikan permission, tetapi pengelolaan role/permission tetap lewat module ini.

## Route dan authorization boundary

Semua route berada di prefix `/access-control` dan wajib `auth`.

| Method | Route | Handler | Backend boundary |
|---|---|---|---|
| GET | `/access-control` | `index` | `can:viewAny,Role` |
| POST | `/access-control/roles` | `storeRole` | `can:create,Role` |
| PUT | `/access-control/roles/{role}` | `updateRole` | `can:update,role` |
| PUT | `/access-control/roles/{role}/permissions` | `syncPermissions` | `can:update,role` |
| DELETE | `/access-control/roles/{role}` | `destroyRole` | `can:delete,role` + explicit `super-system` abort |
| POST | `/access-control/permissions` | `storePermission` | `can:access-control.manage` |
| DELETE | `/access-control/permissions/{permission}` | `destroyPermission` | `can:access-control.manage` + explicit `roles.manage` abort |

Policy mapping:

- `viewAny`: `roles.manage` atau `access-control.view`;
- `create`: `roles.manage` atau `access-control.create`;
- `update`: target bukan `super-system` dan actor punya `roles.manage` atau `access-control.update`;
- `delete`: target bukan `super-system` dan actor punya `roles.manage` atau `access-control.delete`;
- `access-control.manage`: hanya `roles.manage`.

Catatan penting: permission CRUD masih memakai gate internal `access-control.manage`, sehingga hanya actor dengan `roles.manage` yang dapat membuat/menghapus permission. Ini lebih ketat daripada role CRUD granular.

## Role `super-system`

Role `super-system` adalah protected role.

Perlindungan yang sudah ada:

- read model `AccessControlService::getPageData()` menyembunyikan role `super-system` untuk actor non-super-system;
- akun super-system tetap dapat melihat role tersebut dengan flag `is_protected: true`;
- `AccessControlPolicy::update()` dan `delete()` menolak target role `super-system`;
- `StoreRoleRequest` dan `UpdateRoleRequest` menolak `name = super-system`;
- `destroyRole()` memiliki explicit abort tambahan untuk `super-system`;
- frontend menampilkan protected badge dan menonaktifkan reset/save/delete untuk role protected.

Implikasi operasional:

- admin biasa tidak merasa role `super-system` “hilang rusak”; role memang disembunyikan dari boundary non-super-system;
- akun super-system tetap dapat mengaudit keberadaan role tersebut;
- permission role `super-system` tidak boleh diedit dari UI Access Control biasa.

## Permission contract

`permissions.php` mendefinisikan:

- `roles.manage`;
- `access-control.view`;
- `access-control.create`;
- `access-control.update`;
- `access-control.delete`.

Seed default:

- role `admin`: `access-control.view`;
- role `staff`: kosong.

`roles.manage` bertindak sebagai override/manage permission tertinggi untuk Access Control. Permission granular dipakai untuk operasi role, sementara permission creation/deletion tetap dibatasi ke `roles.manage`.

## Frontend behavior

Page `console/access-control/index.tsx` menghitung ability dari shared Inertia props:

- `auth.super`;
- `auth.permissions['roles.manage']`;
- `auth.permissions['access-control.create']`;
- `auth.permissions['access-control.update']`;
- `auth.permissions['access-control.delete']`.

Komponen utama:

- `RoleControlCard`: memilih role, melihat summary, reset/save, delete role;
- `PermissionModulePanel`: group permission per module, checkbox group dan individual permission;
- `RolePermissionWorkspace`: menjaga state permission dan mencegah toggle/save jika role protected atau actor tidak boleh update;
- `AddRoleDialog`: membuat role baru;
- `DeleteRoleDialog`: konfirmasi delete role.

UI rules:

- role protected tidak bisa di-save/reset/delete dari UI;
- permission checkbox disabled jika actor tidak boleh update atau target role protected;
- shortcut keyboard delete juga menolak role protected;
- permission panel dikelompokkan berdasarkan prefix permission sebelum tanda titik.

## Audit dan transaction

Mutation Access Control memakai `AccessControlTransaction` melalui `AccessControlService`.

Event audit yang dicatat:

- `role.created`;
- `role.updated`;
- `role.permissions_synced`;
- `role.deleted`;
- `permission.created`;
- `permission.deleted`.

Setelah mutation, Spatie permission cache dibersihkan dengan `PermissionRegistrar::forgetCachedPermissions()`.

## Acceptance criteria Task 02

- [x] Role/permission CRUD terdokumentasi.
- [x] `super-system` visible hanya untuk akun super-system.
- [x] `super-system` tidak bisa dibuat, rename, delete, atau sync permission via UI biasa.
- [x] Permission module panel dan grouping terdokumentasi.

## Evidence

```bash
php artisan test --filter=AccessControl
```

Hasil:

- 7 tests passed;
- 40 assertions;
- mencakup authorized view/create/sync, unauthorized mutation denial, dan protected `super-system` behavior.

```bash
php artisan module:validate
```

Hasil:

- all module contracts are valid.

```bash
npm run typecheck
```

Hasil:

- TypeScript compile check lulus.

```bash
git diff --check
```

Hasil:

- tidak ada whitespace error.

## Temuan dan guide-plan koreksi

Tidak ada blocking issue pada Task 02. Ada beberapa follow-up kecil yang bisa dikerjakan nanti:

1. Label breadcrumb/page masih memakai “Access Control”, sementara sidebar sudah “Kontrol Akses”.
   - Rekomendasi: samakan label ke Bahasa Indonesia saat polish Console UI.
   - Risiko: rendah, UI copy-only.

2. Permission CRUD memakai gate internal `access-control.manage`, tetapi permission publik yang tersedia adalah `roles.manage`.
   - Rekomendasi: dokumentasi sudah mencatat ini sebagai boundary lebih ketat. Jika nanti ingin granular, buat ADR kecil sebelum mengubahnya.
   - Risiko: sedang jika diubah tanpa test denial matrix.

3. Add role dialog belum memberi hint eksplisit bahwa `super-system` tidak boleh dibuat.
   - Rekomendasi: optional UX copy, backend sudah menolak.
   - Risiko: rendah.

4. Permission module label memakai `Str::headline()` dari prefix permission, belum memakai kamus terjemahan module.
   - Rekomendasi: optional polish agar label permission lebih ramah user Indonesia.
   - Risiko: rendah, read model/UI only.

## Link relevansi

- [README Console](README.md)
- [Tasks Console](tasks.md)
- [Specification Console](specification.md)
- [Mutation authorization matrix global](../../reviews/2026-07-11-project-baseline/08-mutation-authorization-matrix.md)
- [Project module guide](../../guides/project-module-guide.md)
