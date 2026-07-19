# Checkpoint A — Identity and Access Boundary

Tanggal checkpoint: 2026-07-19  
Status: pass untuk dokumentasi baseline Console Task 01–03

Checkpoint ini mengunci hasil telusur awal Console: shell/auth/dashboard, Access Control, dan User Management. Tujuannya memastikan fondasi identity dan authorization sudah cukup jelas sebelum lanjut ke System Settings.

## Scope checkpoint

Dokumen yang menjadi input:

1. [01 — Console Shell Baseline](01-console-shell-baseline.md)
2. [02 — Access Control Boundary](02-access-control-boundary.md)
3. [03 — User Management Lifecycle](03-user-management-lifecycle.md)

Area source yang menjadi fokus:

- `/dashboard` dan `/hr/dashboard` route baseline;
- shared Inertia auth props;
- `Console.AccessControls`;
- `Console.UserManagements`;
- profile settings yang terkait avatar/password/delete account;
- protected role `super-system`;
- permission denial matrix untuk role/user mutation;
- impersonation safety.

## Kesimpulan

Checkpoint A dinyatakan pass.

Fondasi identity dan access boundary sudah memenuhi baseline:

- Console dashboard dan HR dashboard terpisah secara route dan permission;
- shared auth props menyediakan user, roles, permissions, `super`, dan impersonation state;
- Access Control mengelola role/permission dengan backend policy/gate;
- User Management mengelola user lifecycle tanpa membuka jalur assign `super-system`;
- role `super-system` hidden bagi actor non-super-system, tetap visible untuk akun super-system;
- user/password lifecycle tidak mengirim password plaintext dari admin form;
- impersonation punya guard terhadap target super-system, target diri sendiri, dan nested impersonation;
- mutation penting dicatat ke audit service;
- tidak ada blocking security/correctness issue pada Task 01–03.

## Acceptance criteria checkpoint

- [x] Task 01–03 selesai.
- [x] Protected role policy/visibility sudah terdokumentasi.
- [x] Denial matrix users/access-control hijau.
- [x] Tidak ada secret/password/token masuk response/audit pada area checkpoint.

## Evidence

```bash
php artisan test --filter="Dashboard|AccessControl|UserManagement|UserImpersonation"
```

Hasil:

- 27 tests passed;
- 130 assertions.

```bash
php artisan test --filter="UserManagement|UserImpersonation|ProfileUpdate|PasswordUpdate"
```

Hasil dari Task 03:

- 29 tests passed;
- 151 assertions.

```bash
vendor/bin/pint --test app/Modules/Console/UserManagements app/Models/User.php tests/Feature/UserManagementTest.php tests/Feature/UserImpersonationTest.php
```

Hasil:

- passed.

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
npm run build
```

Hasil:

- Vite production build lulus.

```bash
rg -n "password|token|secret|remember_token|mail_password|api_key" \
  app/Modules/Console/AccessControls \
  app/Modules/Console/UserManagements \
  app/Http/Middleware/HandleInertiaRequests.php \
  app/Models/User.php \
  app/Http/Controllers/Settings \
  app/Http/Requests/Settings \
  tests/Feature/AccessControlTest.php \
  tests/Feature/UserManagementTest.php \
  tests/Feature/UserImpersonationTest.php \
  tests/Feature/Settings/ProfileUpdateTest.php \
  tests/Feature/Settings/PasswordUpdateTest.php
```

Hasil review:

- temuan hanya referensi validasi, hashing, hidden field, test password fixture, dan flag `send_password_reset_link`;
- tidak ditemukan response/audit yang menyimpan password plaintext, token reset, remember token, secret, mail password, atau API key pada area checkpoint;
- audit User Management hanya mencatat metadata aman seperti roles, permissions, avatar changed/removed, activation/reset link requested, dan email sebagai identifier actor/target.

## Threat model ringkas

| Threat | Status | Boundary |
|---|---|---|
| Guest mengakses dashboard | tertutup | `/dashboard` wajib `auth`; guest redirect login |
| Actor non-authorized mutate role | tertutup | `AccessControlPolicy`, controller middleware, tests |
| Actor membuat/mengubah role `super-system` | tertutup | request validation + policy + controller abort |
| Actor non-super-system melihat role `super-system` | tertutup | read model filtering |
| Actor assign `super-system` dari User Management | tertutup | request validation + role filtering |
| Admin mengubah password user manual | tertutup | payload password diabaikan; reset via link |
| Impersonate target super-system | tertutup | `UserImpersonationService::canStart()` |
| Nested impersonation | tertutup | session guard |
| Self-delete account saat setting disabled | tertutup | `ProfileController::destroy()` abort 403 |
| Password/token/secret leakage area checkpoint | tidak ditemukan | hidden model fields, scan source, audit review |

## Residual risk dan guide-plan

Tidak ada blocker sebelum lanjut ke Task 04. Follow-up yang tetap dicatat:

1. Samakan validasi avatar User Management dengan profile settings.
   - Kondisi sekarang: User Management `image|max:2048`; profile settings `image|mimes:jpg,jpeg,png,webp|max:2048`.
   - Guide-plan: ubah request User Management menjadi allowlist MIME eksplisit, tambah test SVG/unsupported MIME ditolak.

2. Poles bahasa UI Console identity/access.
   - Kondisi sekarang: masih ada campuran “Access Control/User/User Management/Manajemen User”.
   - Guide-plan: setelah semua Console module selesai ditelusuri, lakukan copy polish mekanis.

3. Hubungkan `lastLogin` User Management dengan Login Activities.
   - Kondisi sekarang: `lastLogin` masih `null`.
   - Guide-plan: kerjakan saat Task 08 Login Activities agar read model tidak spekulatif.

4. Audit retention dan PII minimization belum diformalkan.
   - Kondisi sekarang: audit impersonation menyimpan email actor/target untuk traceability.
   - Guide-plan: bahas pada Task 07 Audit Logs; jika perlu buat ADR retention/masking.

5. Global search/help pada header Console belum aktif.
   - Kondisi sekarang: placeholder UX dari Task 01.
   - Guide-plan: jadikan backlog polish setelah operational foundation selesai.

## Keputusan checkpoint

Lanjut ke Task 04 — System Settings configuration boundary.

Alasannya: identity, access control, protected role, user lifecycle, dan impersonation sudah memiliki test coverage yang cukup untuk baseline; sisa temuan adalah polish/hardening kecil yang tidak menghalangi penelusuran module Console berikutnya.
