# 03 — User Management Lifecycle

Tanggal telusur: 2026-07-19  
Status: selesai untuk baseline dokumentasi Task 03

Dokumen ini mencatat hasil telusur module `Console/UserManagements` dan area profile settings yang terkait langsung dengan user lifecycle. Penulisan dibuat seolah-olah User Management baru akan dibangun, tetapi isi dokumen mengikuti source aktual.

## Ringkasan hasil

User Management adalah boundary identity operasional Console. Module ini mengelola akun login, role/direct permission assignment, avatar, activation/password reset link, archive/restore/force-delete, dan impersonation.

Boundary utamanya sudah jelas:

1. semua route User Management wajib `auth`;
2. page/action dilindungi `UserPolicy` dan FormRequest authorization;
3. assignment role `super-system` ditolak dari payload User Management;
4. role `super-system` disembunyikan dari actor non-super-system;
5. impersonation menolak target super-system, target diri sendiri, dan nested impersonation;
6. password tidak pernah diatur manual dari User Management; user menerima activation/reset link.

## File sumber yang ditelusuri

Backend:

- `app/Modules/Console/UserManagements/module.php`
- `app/Modules/Console/UserManagements/routes.php`
- `app/Modules/Console/UserManagements/permissions.php`
- `app/Modules/Console/UserManagements/navigation.php`
- `app/Modules/Console/UserManagements/Providers/UserManagementsServiceProvider.php`
- `app/Modules/Console/UserManagements/Policies/UserPolicy.php`
- `app/Modules/Console/UserManagements/Http/Controllers/UserController.php`
- `app/Modules/Console/UserManagements/Http/Requests/StoreUserRequest.php`
- `app/Modules/Console/UserManagements/Http/Requests/UpdateUserRequest.php`
- `app/Modules/Console/UserManagements/DTO/UserData.php`
- `app/Modules/Console/UserManagements/Services/UserService.php`
- `app/Modules/Console/UserManagements/Services/UserImpersonationService.php`
- `app/Modules/Console/UserManagements/Transactions/UserTransaction.php`
- `app/Models/User.php`
- `app/Http/Controllers/Settings/ProfileController.php`
- `app/Http/Requests/Settings/ProfileUpdateRequest.php`
- `app/Modules/Console/SystemSettings/Jobs/SendUserActivationLinkJob.php`
- `app/Modules/Console/SystemSettings/Notifications/UserActivationLinkNotification.php`

Frontend:

- `resources/js/pages/console/users/index.tsx`
- `resources/js/pages/console/users/types.ts`
- `resources/js/pages/console/users/user-components/user-form.tsx`
- `resources/js/pages/console/users/user-components/user-table.tsx`
- `resources/js/pages/console/users/user-components/delete-user-dialog.tsx`
- `resources/js/pages/console/users/user-components/impersonate-user-dialog.tsx`
- `resources/js/pages/settings/profile.tsx`
- `resources/js/components/image-crop-dialog.tsx`

Test:

- `tests/Feature/UserManagementTest.php`
- `tests/Feature/UserImpersonationTest.php`
- `tests/Feature/Settings/ProfileUpdateTest.php`
- `tests/Feature/Settings/PasswordUpdateTest.php`

## Contract module

`module.php` mendefinisikan module:

- project: `Console`;
- title: `Manajemen User`;
- slug: `user-managements`;
- provider: `UserManagementsServiceProvider`;
- dependency: `Console.AccessControls`;
- exports: `routes`, `permissions`, `navigation`.

Dependency ke `Console.AccessControls` benar karena User Management memakai Spatie role/permission yang dikelola oleh Access Control.

## Permission contract

`permissions.php` mendefinisikan:

- `users.view`;
- `users.create`;
- `users.update`;
- `users.delete`;
- `users.restore`;
- `users.force-delete`;
- `users.impersonate`.

Default role mapping:

- `admin`: `users.view`, `users.create`, `users.update`;
- `staff`: `users.view`.

Delete, restore, force-delete, dan impersonate harus diberikan eksplisit; tidak otomatis dimiliki admin dari module permission file ini.

## Route dan lifecycle

Semua route berada di middleware `auth`.

| Method | Route | Handler | Fungsi | Boundary |
|---|---|---|---|---|
| GET | `/users` | `index` | list/filter/read model user | `viewAny User` |
| POST | `/users` | `store` | create user | `StoreUserRequest::authorize()` |
| PUT/PATCH | `/users/{user}` | `update` | update identity, avatar, role, direct permission | `UpdateUserRequest::authorize()` |
| DELETE | `/users/{user}` | `destroy` | soft delete/archive | `authorize('delete', $user)` |
| PATCH | `/users/{user}/restore` | `restore` | restore archived user | `authorize('restore', $target)` |
| DELETE | `/users/{user}/force` | `forceDestroy` | permanent delete archived user | `authorize('forceDelete', $target)` |
| POST | `/users/{user}/impersonate` | `impersonate` | login-as target user | `UserImpersonationService::canStart()` |
| POST | `/users/impersonate/stop` | `stopImpersonating` | return to impersonator | session impersonator check |

## UserPolicy

Policy aktual:

- `viewAny`: actor punya `users.view`;
- `create`: actor punya `users.create`;
- `update`: actor punya `users.update`;
- `delete`: actor bukan target diri sendiri, target belum trashed, actor punya `users.delete`;
- `restore`: target trashed dan actor punya `users.restore`;
- `forceDelete`: actor bukan target diri sendiri, target trashed, actor punya `users.force-delete`;
- `impersonate`: actor bukan target diri sendiri, tidak sedang impersonate, target bukan super-system, actor punya `users.impersonate`.

Catatan: controller impersonation tidak memanggil policy secara langsung, tetapi `UserImpersonationService::canStart()` menerapkan guard yang sama dengan pesan error user-friendly dan audit trail.

## Protected role `super-system`

User Management menghormati protected role dari Access Control:

- actor non-super-system tidak menerima opsi role `super-system`;
- actor non-super-system tidak melihat role `super-system` di listed user roles;
- filter role `super-system` dinetralkan menjadi kosong jika actor bukan super-system;
- `StoreUserRequest` dan `UpdateUserRequest` menolak role payload `super-system`;
- jika target user sudah super-system dan role `super-system` tidak ikut payload update, `UserService::syncRoles()` mempertahankan role tersebut agar tidak terhapus tidak sengaja;
- impersonation menolak target super-system.

Implikasi:

- role `super-system` hanya bisa terlihat lengkap untuk akun super-system;
- User Management tidak menjadi pintu assign/escalation ke super-system;
- update user super-system tidak menghapus role protected secara tidak sengaja.

## Create user

Flow create:

1. actor membuka `/users` dengan `users.view`;
2. actor submit form create jika punya `users.create`;
3. `StoreUserRequest` validasi:
   - `name` required;
   - `email` lowercase, valid, unique;
   - role harus exist guard `web` dan bukan `super-system`;
   - permission harus exist guard `web`;
   - avatar optional image max 2048 KB;
4. `UserService::create()` membuat user dengan password internal acak;
5. role, direct permission, dan avatar disinkronkan;
6. audit `user.created` dicatat;
7. jika email automation aktif, `SendUserActivationLinkJob` dikirim ke queue `mail`.

Password manual dari payload create tidak menjadi bagian DTO. User menerima activation link untuk mengatur password sendiri.

## Update user

Flow update:

1. actor submit edit jika punya `users.update`;
2. `UpdateUserRequest` validasi name, unique email, role, permission, avatar, dan `send_password_reset_link`;
3. `UserService::update()` menyimpan identity, role/direct permission, avatar;
4. audit `user.updated` mencatat old/new values tanpa password;
5. jika `send_password_reset_link = true` dan setting email mengizinkan, job activation/reset link dikirim;
6. password lama tidak berubah ketika payload password manual dikirim.

Ini penting: User Management tidak pernah menjadi form edit password admin. Password tetap boundary milik user lewat reset link/profile password.

## Avatar

User memakai Spatie Media Library:

- `User` implements `HasMedia`;
- media collection `avatar` single file;
- accessor `avatar` mengembalikan URL media pertama;
- User Management create/update menerima avatar image max 2048 KB;
- Profile settings menerima avatar dengan mimes `jpg`, `jpeg`, `png`, `webp` dan max 2048 KB;
- frontend memakai `ImageCropDialog` untuk crop avatar sebelum upload;
- remove avatar membersihkan collection `avatar`.

Catatan security:

- avatar masih memakai validasi Laravel `image`;
- profile settings lebih ketat karena ada MIME allowlist eksplisit;
- User Management saat ini belum punya `mimes` eksplisit seperti profile settings.

## Archive, restore, dan force-delete

`User` memakai `SoftDeletes`.

Lifecycle:

- active user dapat diarsipkan jika actor punya `users.delete` dan bukan target diri sendiri;
- archived user dapat direstore jika actor punya `users.restore`;
- archived user dapat dihapus permanen jika actor punya `users.force-delete` dan bukan target diri sendiri;
- force delete dilakukan setelah audit `user.force-deleted` dicatat.

UI:

- filter arsip: active, with trashed, only trashed;
- archived user menampilkan aksi restore/force-delete sesuai `user.can`;
- delete dialog membedakan “Arsipkan User” dan “Hapus Permanen User”.

## Activation dan password reset link

Email link dikendalikan oleh System Settings:

- `shouldSendCredentialsOnCreate()`;
- `shouldSendCredentialsOnPasswordUpdate()`.

Jika enabled:

1. `UserService` dispatch `SendUserActivationLinkJob`;
2. job membuat password reset token melalui Laravel Password broker;
3. `UserActivationLinkNotification` mengirim mail dengan route `password.reset`;
4. template `user.activation` dirender lewat `NotificationTemplateService`;
5. pada local/log mail, link muncul di `storage/logs/laravel.log`.

Dokumen ini mengikuti keputusan operasional sebelumnya: sementara email dapat diarahkan ke log local saat pengujian.

## Impersonation

Flow start:

1. actor klik login-as jika punya `users.impersonate` dan row `can.impersonate = true`;
2. dialog menjelaskan semua start/stop tercatat audit;
3. `UserImpersonationService::canStart()` menolak:
   - belum login;
   - target diri sendiri;
   - target super-system;
   - sedang impersonate user lain;
   - actor tanpa `users.impersonate`;
4. session menyimpan impersonator id/name/email;
5. Laravel Auth login ke target;
6. session regenerate;
7. audit `user.impersonation_started` dicatat dengan actor impersonator.

Flow stop:

1. service membaca `impersonator_id` dari session;
2. Auth kembali ke impersonator;
3. session impersonation dibersihkan;
4. session regenerate;
5. audit `user.impersonation_stopped` dicatat.

Boundary penting:

- target super-system tidak bisa diimpersonate;
- super-system boleh impersonate user non-super-system;
- nested impersonation ditolak;
- impersonation memakai session server-side, bukan token client-side.

## Profile settings yang terkait User Management

Halaman `/settings/profile` bukan bagian module `UserManagements`, tetapi menjadi lifecycle self-service user:

- user dapat update name/email;
- perubahan email mengosongkan `email_verified_at`;
- user dapat upload/remove avatar;
- user dapat delete own account jika System Settings `allow_account_deletion` aktif;
- delete own account memerlukan current password, logout, invalidate session, dan regenerate CSRF token.

Ini perlu dicatat karena fitur avatar dan delete account user pernah dipoles di luar module User Management.

## Acceptance criteria Task 03

- [x] User lifecycle dan soft delete policy jelas.
- [x] Role protected tidak assignable dari payload user management.
- [x] Avatar upload/crop/remove terdokumentasi.
- [x] Password reset/activation link behavior jelas untuk email/log local.
- [x] Impersonation menolak target super-system untuk non-super-system.

## Evidence

```bash
php artisan test --filter="UserManagement|UserImpersonation|ProfileUpdate|PasswordUpdate"
```

Hasil:

- 29 tests passed;
- 151 assertions;
- mencakup user create/update/avatar/archive/restore/force-delete, reset link queue, manual password payload ignored, profile avatar, account deletion setting, password update, dan impersonation.

```bash
vendor/bin/pint --test app/Modules/Console/UserManagements app/Models/User.php tests/Feature/UserManagementTest.php tests/Feature/UserImpersonationTest.php
```

Hasil:

- passed.

## Temuan dan guide-plan koreksi

Tidak ada blocking issue pada Task 03. Follow-up kecil:

1. `StoreUserRequest` dan `UpdateUserRequest` untuk User Management memakai validasi `image|max:2048`, tetapi belum eksplisit `mimes:jpg,jpeg,png,webp` seperti profile settings.
   - Rekomendasi: samakan allowlist avatar User Management dengan profile settings.
   - Risiko: rendah-sedang; perubahan validasi dapat menolak format yang sebelumnya diterima.

2. Breadcrumb/page title masih campuran “User”, “User Management”, dan “Manajemen User”.
   - Rekomendasi: polish copy Bahasa Indonesia setelah semua Console module selesai ditelusuri.
   - Risiko: rendah.

3. `lastLogin` pada read model User Management masih `null`.
   - Rekomendasi: pada Task Login Activities, pertimbangkan menghubungkan last successful login sebagai read model.
   - Risiko: rendah; read-only improvement.

4. Audit impersonation mencatat email actor dan target. Ini bukan password/token, tetapi termasuk identifier personal.
   - Rekomendasi: tetap boleh untuk audit keamanan; jika butuh minimisasi PII lebih ketat, buat ADR retention/masking audit.
   - Risiko: sedang pada organisasi dengan kebijakan PII ketat.

5. Stop impersonation route tidak memakai permission karena harus bisa dilakukan oleh session yang sedang impersonate.
   - Rekomendasi: pertahankan; boundary-nya adalah session `impersonator_id`.
   - Risiko: rendah jika session lifecycle tetap benar.

## Link relevansi

- [README Console](README.md)
- [Tasks Console](tasks.md)
- [02 — Access Control Boundary](02-access-control-boundary.md)
- [Specification Console](specification.md)
- [System Settings task](tasks.md#task-04--system-settings-configuration-boundary)
- [Audit Logs task](tasks.md#task-07--audit-logs-immutable-boundary)
