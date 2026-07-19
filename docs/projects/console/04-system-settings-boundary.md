# 04 — System Settings Configuration Boundary

Tanggal telusur: 2026-07-19  
Status: selesai; hardening secret map/maintenance diterapkan

Dokumen ini mencatat hasil telusur module `Console/SystemSettings` seolah-olah module baru akan dibangun. Fokusnya adalah konfigurasi runtime, permission boundary, secret masking, delete account visibility, email/log behavior, dan health/environment panel.

## Ringkasan hasil

System Settings adalah pusat konfigurasi runtime Console. Module ini mengelola:

- email/mailer dan activation/reset link automation;
- branding aplikasi;
- localization/timezone/date format;
- pagination default;
- security policy;
- password policy;
- maintenance mode;
- map configuration;
- system health dan environment info.

Boundary umum sudah baik:

- semua route wajib `auth`;
- view dilindungi `system-settings.view`;
- mutation/test email dilindungi `system-settings.update`;
- setiap update masuk audit log;
- email SMTP password disimpan terenkripsi dan tidak dikirim balik ke frontend;
- Google Maps API key disimpan terenkripsi, masked pada System Settings props/audit, dan hanya dibuka melalui method runtime eksplisit untuk consumer map;
- maintenance secret disimpan terenkripsi, masked pada System Settings props/audit, dan update kosong mempertahankan secret lama;
- profile delete account mengikuti `allow_account_deletion`;
- password policy sudah dipakai oleh profile/password reset.

## File sumber yang ditelusuri

Backend:

- `app/Modules/Console/SystemSettings/module.php`
- `app/Modules/Console/SystemSettings/routes.php`
- `app/Modules/Console/SystemSettings/permissions.php`
- `app/Modules/Console/SystemSettings/navigation.php`
- `app/Modules/Console/SystemSettings/Providers/SystemSettingsServiceProvider.php`
- `app/Modules/Console/SystemSettings/Policies/SystemSettingPolicy.php`
- `app/Modules/Console/SystemSettings/Http/Controllers/SystemSettingController.php`
- `app/Modules/Console/SystemSettings/Http/Requests/*`
- `app/Modules/Console/SystemSettings/DTO/*`
- `app/Modules/Console/SystemSettings/Models/SystemSetting.php`
- `app/Modules/Console/SystemSettings/Services/SystemSettingService.php`
- `app/Modules/Console/SystemSettings/Services/SystemHealthService.php`
- `app/Modules/Console/SystemSettings/Jobs/SendUserActivationLinkJob.php`
- `app/Modules/Console/SystemSettings/Notifications/UserActivationLinkNotification.php`
- `app/Http/Controllers/Settings/ProfileController.php`
- `app/Http/Requests/Settings/ProfileUpdateRequest.php`

Frontend:

- `resources/js/pages/console/system-settings/index.tsx`
- `resources/js/pages/console/system-settings/types.ts`
- `resources/js/pages/console/system-settings/components-system-settings/*`
- `resources/js/pages/settings/profile.tsx`

Test:

- `tests/Feature/SystemSettingTest.php`
- `tests/Feature/Settings/ProfileUpdateTest.php`
- `tests/Feature/Settings/PasswordUpdateTest.php`

## Contract module

`module.php` mendefinisikan:

- project: `Console`;
- title: `System Settings`;
- slug: `system-settings`;
- provider: `SystemSettingsServiceProvider`;
- dependencies: kosong;
- exports: `routes`, `permissions`, `navigation`.

System Settings tidak bergantung ke module bisnis. Module bisnis boleh membaca behavior global seperti pagination atau email automation melalui service, tetapi tidak boleh menulis setting langsung tanpa route/policy System Settings.

## Permission contract

`permissions.php` mendefinisikan:

- `system-settings.view`;
- `system-settings.update`.

Default role mapping:

- `admin`: `system-settings.view`;
- `staff`: kosong.

Artinya update harus diberikan eksplisit. Ini baik karena setting runtime termasuk area sensitif.

## Route dan authorization boundary

Semua route memakai middleware `auth` dan prefix `/system-settings`.

| Method | Route | Handler | Fungsi | Boundary |
|---|---|---|---|---|
| GET | `/system-settings` | `index` | render semua panel settings | `authorize('view', SystemSettingService::class)` |
| PUT | `/system-settings/email` | `updateEmail` | update mailer/SMTP/template flags | `UpdateEmailSettingRequest::authorize()` |
| POST | `/system-settings/email/test` | `testEmail` | kirim email test | `TestEmailSettingRequest::authorize()` |
| PUT | `/system-settings/branding` | `updateBranding` | app name/logo/favicon | `UpdateBrandingSettingRequest::authorize()` |
| PUT | `/system-settings/localization` | `updateLocalization` | timezone/date/time format | `UpdateLocalizationSettingRequest::authorize()` |
| PUT | `/system-settings/pagination` | `updatePagination` | default per page/options | `UpdatePaginationSettingRequest::authorize()` |
| PUT | `/system-settings/security-policy` | `updateSecurityPolicy` | session/login/delete account policy | `UpdateSecurityPolicyRequest::authorize()` |
| PUT | `/system-settings/password-policy` | `updatePasswordPolicy` | password rule policy | `UpdatePasswordPolicyRequest::authorize()` |
| PUT | `/system-settings/maintenance-mode` | `updateMaintenanceMode` | Laravel down/up config | `UpdateMaintenanceModeRequest::authorize()` |
| PUT | `/system-settings/map` | `updateMap` | Google Maps config | `UpdateMapSettingRequest::authorize()` |

`SystemSettingPolicy`:

- `view`: actor punya `system-settings.view`;
- `update`: actor punya `system-settings.update`.

## Data model

`SystemSetting` menyimpan pasangan:

- `group`;
- `key`;
- `value`;
- `encrypted`.

Model juga memakai Spatie Media Library untuk:

- `branding_logo`;
- `branding_favicon`.

`SystemSettingService::put()` menyimpan value sebagai string. Jika flag encrypted aktif dan value terisi, value dienkripsi dengan `Crypt::encryptString()`.

## Email settings

Email defaults:

- `enabled`;
- `mailer`: `smtp`, `log`, `array`;
- SMTP host/port/username/password/encryption;
- sender address/name;
- flags `send_credentials_on_create` dan `send_credentials_on_password_update`;
- subject/intro activation credential.

Boundary baik:

- SMTP password ada di `ENCRYPTED_KEYS`;
- `emailSettings()` default tidak mengembalikan password;
- frontend hanya menerima `password_configured`;
- form password dikosongkan dengan placeholder “password sudah tersimpan”;
- audit memakai `safeEmailSettingsForAudit()` dan menghapus field `password`;
- update password SMTP bersifat optional: kosong berarti password lama tidak diubah;
- mailer `log` dan `array` tersedia untuk development/local testing.

Email automation terkait User Management:

- create user dapat dispatch activation link jika email enabled dan `send_credentials_on_create = true`;
- update user dapat dispatch reset link jika requested dan `send_credentials_on_password_update = true`;
- link dibuat melalui Laravel Password broker di `SendUserActivationLinkJob`.

## Branding settings

Branding mengelola:

- `app_name`;
- logo;
- favicon.

Logo/favicon disimpan sebagai media pada record synthetic `branding/assets`.

Boundary:

- update wajib `system-settings.update`;
- audit mencatat old/new branding;
- shared Inertia props mengambil branding untuk shell UI.

## Localization settings

Localization mengelola:

- timezone;
- date format;
- time format;
- preview date/time/datetime.

`applyLocalizationSettings()` memperbarui `config('app.timezone')` dan `date_default_timezone_set()`.

## Pagination settings

Pagination mengelola:

- `default_per_page`;
- `per_page_options`.

`resolvePerPage()` memastikan request `per_page` hanya dipakai jika masuk allowlist options. Jika tidak valid, fallback ke default.

## Security policy

Security policy mengelola:

- `require_email_verification`;
- `audit_sensitive_actions`;
- `single_session_per_user`;
- `allow_account_deletion`;
- `session_lifetime_minutes`;
- `login_max_attempts`;
- `login_decay_minutes`;
- `password_confirmation_timeout_seconds`.

`accountDeletionEnabled()` dipakai oleh profile settings:

- page `/settings/profile` menerima prop `accountDeletionEnabled`;
- jika false, delete account section dapat disembunyikan;
- endpoint delete account juga abort 403 saat disabled.

Catatan implementasi:

- `applySecurityPolicy()` mengatur session lifetime dan password confirmation timeout.
- `login_max_attempts`, `login_decay_minutes`, `single_session_per_user`, dan `require_email_verification` tersimpan sebagai policy, tetapi enforcement penuh perlu ditelusuri pada task auth/login terkait.

## Password policy

Password policy mengelola:

- min length;
- uppercase;
- lowercase;
- numbers;
- symbols;
- uncompromised;
- expiry days;
- history count.

Enforcement saat ini:

- min length aktif;
- uppercase/lowercase aktif;
- numbers aktif;
- symbols aktif;
- uncompromised aktif jika enabled;
- dipakai oleh `PasswordController` melalui `SystemSettingService::passwordRules()`.

Belum enforced:

- expiry days;
- password history.

Frontend sudah memberi catatan “disimpan untuk tahap enforcement berikutnya”.

## Maintenance mode

Maintenance mode mengelola:

- enabled;
- message;
- page style;
- retry seconds;
- refresh seconds;
- secret;
- active state;
- bypass URL.

`applyMaintenanceMode()` memanggil `artisan down/up` kecuali saat unit test. Saat enabled, service mengoper:

- `--render=errors::503`;
- `--retry`;
- `--refresh` jika ada;
- `--secret` jika ada.

Hardening:

- `secret` masuk encrypted key dan disimpan encrypted at rest;
- `maintenanceModeSettings()` default mengembalikan `secret = null`, `secret_configured`, dan `bypass_url = null`;
- update secret kosong mempertahankan secret lama;
- audit old/new values tidak menyertakan nilai secret atau bypass URL;
- `applyMaintenanceMode()` tetap dapat memakai secret melalui `maintenanceModeSettings(includeSecret: true)`.

## Map settings

Map settings mengelola:

- enabled;
- `google_maps_api_key`;
- `google_maps_map_id`;
- configured flag.

Hardening:

- `google_maps_api_key` masuk encrypted key dan disimpan encrypted at rest;
- `mapSettings()` default mengembalikan `google_maps_api_key = null` dan `configured`;
- update API key kosong mempertahankan key lama;
- audit `map.updated` tidak menyertakan key plaintext;
- HR Work Locations memakai method eksplisit `mapRuntimeSettings()` untuk kebutuhan runtime Google Maps JavaScript API.

Catatan: Google Maps JavaScript API browser key tetap harus tersedia bagi consumer map runtime agar peta bisa dimuat di browser. Karena itu exposure runtime dibuat eksplisit lewat `mapRuntimeSettings()`, bukan dari props admin System Settings.

## System health dan environment info

Panel health/environment membaca runtime:

- database/storage/cache/queue/logs;
- app environment/debug/timezone;
- PHP/Laravel/runtime info.

Boundary:

- hanya actor dengan `system-settings.view` dapat melihatnya;
- tidak ada mutation di panel health/environment.

Catatan:

- environment info perlu tetap dijaga supaya tidak menampilkan secret `.env`.
- telusur detail `SystemHealthService` tidak menemukan penggunaan secret credential secara langsung pada Task 04.

## Acceptance criteria Task 04

- [x] Branding/localization/pagination/password/security/email/map/maintenance terdokumentasi.
- [x] Secret email/map tidak dikirim plaintext ke frontend System Settings.
  - Email password: encrypted/masked.
  - Map API key: encrypted/masked pada System Settings props/audit; runtime map consumer memakai method eksplisit.
  - Maintenance secret: encrypted/masked pada System Settings props/audit.
- [x] Delete account visibility setting terdokumentasi.
- [x] Email automation local/log behavior terdokumentasi.

## Evidence

```bash
php artisan test --filter="SystemSetting|ProfileUpdate"
```

Hasil:

- 23 tests passed;
- 150 assertions.

```bash
vendor/bin/pint --test app/Modules/Console/SystemSettings app/Modules/HR/WorkLocations tests/Feature/SystemSettingTest.php
```

Hasil:

- passed.

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
php artisan module:validate
```

Hasil:

- all module contracts are valid.

```bash
git diff --check
```

Hasil:

- tidak ada whitespace error.

## Temuan dan guide-plan koreksi

### Fixed — Masking/encryption map key

Koreksi diterapkan:

- `google_maps_api_key` ditambahkan ke encrypted keys;
- database menyimpan encrypted value;
- System Settings props mengembalikan `google_maps_api_key = null` dan `configured`;
- update key kosong mempertahankan key lama;
- audit tidak menyimpan key plaintext;
- frontend memakai placeholder “API Key sudah tersimpan”;
- test regression ditambahkan untuk database encryption, props masking, audit masking, dan blank update retain.

Catatan runtime:

- HR Work Locations tetap dapat memuat Google Maps melalui `mapRuntimeSettings()`;
- method ini sengaja eksplisit agar exposure browser key tidak tercampur dengan admin settings props.

### Fixed — Masking maintenance secret dan bypass URL

Koreksi diterapkan:

- maintenance `secret` ditambahkan ke encrypted keys;
- database menyimpan encrypted value;
- System Settings props mengembalikan `secret = null`, `secret_configured`, dan `bypass_url = null`;
- update secret kosong mempertahankan secret lama;
- audit tidak menyimpan nilai secret atau full bypass URL;
- frontend tidak lagi menampilkan bypass URL lama, hanya status secret tersimpan;
- test regression ditambahkan untuk database encryption, props masking, audit masking, dan blank update retain.

### Optional — Enforcement security policy lanjutan

Kondisi sekarang:

- session lifetime dan password confirmation timeout diterapkan;
- login max attempts/decay disimpan tetapi perlu telusur pada auth login task;
- single session dan require email verification belum dikonfirmasi enforcement-nya pada Task 04.

Guide-plan:

- telusuri saat task auth/login atau security observability;
- jika belum enforced, ubah label menjadi “planned” atau implement incremental dengan test.

### Optional — Password expiry/history enforcement

Kondisi sekarang:

- UI sudah menyatakan expiry/history disimpan untuk tahap berikutnya.

Guide-plan:

- buat task terpisah jika ingin enforcement password rotation/history.

### Optional — Bahasa UI

Kondisi sekarang:

- menu/sidebar memakai “System Settings”; beberapa panel bercampur Bahasa Indonesia/Inggris.

Guide-plan:

- polish copy Console setelah semua module selesai ditelusuri.

## Link relevansi

- [README Console](README.md)
- [Tasks Console](tasks.md)
- [Checkpoint A — Identity and Access Boundary](checkpoint-a-identity-access-boundary.md)
- [03 — User Management Lifecycle](03-user-management-lifecycle.md)
- [Specification Console](specification.md)
- [Task 07 — Audit Logs](tasks.md#task-07--audit-logs-immutable-boundary)
- [Task 08 — Login Activities](tasks.md#task-08--login-activities-security-observability)
