# 08 — Login Activities Security Observability

Dokumen ini mencatat hasil telusur `Console.LoginActivities` seolah-olah modul ini baru akan dibuat. Fokusnya adalah memastikan login sukses, login gagal, dan logout tercatat untuk observability keamanan tanpa menyimpan password/token.

## Status

`Reviewed and covered — 2026-07-19`.

Task dinyatakan selesai. Ada tambahan test untuk denial view, logout recording, dan jaminan password request tidak tersimpan pada login activity.

## Urutan baca relevan

1. [07 — Audit Logs Immutable Boundary](07-audit-logs-immutable-boundary.md) — observability mutation umum.
2. Dokumen ini — observability khusus authentication.
3. Nanti: [Checkpoint C — Observability boundary](tasks.md#checkpoint-c--observability-boundary).
4. Nanti: [Task 09 — Queue Monitor runtime control](tasks.md#task-09--queue-monitor-runtime-control).

## Source yang ditelusuri

- `app/Modules/Console/LoginActivities/module.php`
- `app/Modules/Console/LoginActivities/routes.php`
- `app/Modules/Console/LoginActivities/permissions.php`
- `app/Modules/Console/LoginActivities/navigation.php`
- `app/Modules/Console/LoginActivities/Models/LoginActivity.php`
- `app/Modules/Console/LoginActivities/Policies/LoginActivityPolicy.php`
- `app/Modules/Console/LoginActivities/Providers/LoginActivitiesServiceProvider.php`
- `app/Modules/Console/LoginActivities/Http/Controllers/LoginActivityController.php`
- `app/Modules/Console/LoginActivities/Services/LoginActivityService.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `resources/js/pages/console/login-activities/index.tsx`
- `resources/js/pages/console/login-activities/login-activity-components/login-summary-cards.tsx`
- `resources/js/pages/console/login-activities/login-activity-components/login-activity-header.tsx`
- `tests/Feature/LoginActivityTest.php`

## Contract modul

`Console.LoginActivities` adalah read-only security observability module untuk melihat histori akses user.

Boundary utama:

- route user-facing hanya `GET /login-activities`;
- view wajib `login-activities.view`;
- tidak ada create/update/delete route untuk user;
- recording dilakukan internal oleh auth controller lewat `LoginActivityService`;
- tidak menyimpan password, reset token, session token, remember token, atau credential secret;
- menyimpan metadata keamanan seperti email, event, success flag, IP, user agent, device, browser, platform, message, dan waktu kejadian.

## Module export

| Export | Status | Catatan |
|---|---:|---|
| `routes` | yes | `GET login-activities` |
| `permissions` | yes | `login-activities.view` |
| `navigation` | yes | menu Observability → Login Activity |
| `events/listeners` | no | recording dipanggil eksplisit dari auth controller |
| `integrations` | no | bukan contract eksternal |

## Route dan permission

| Route | Method | Aksi | Guard |
|---|---:|---|---|
| `/login-activities` | `GET` | list/filter/detail login activity | `auth` + policy `viewAny` |

Permission:

- `login-activities.view`

Default role mapping:

- `admin`: mendapat `login-activities.view`;
- `staff`: tidak mendapat permission default.

## Event yang dicatat

| Event | Sumber | Successful | User relation | Email source |
|---|---|---:|---|---|
| `login` | login berhasil Console/HR | true | user login | user email |
| `login_failed` | login gagal Console/HR | false | user jika email cocok | request email |
| `logout` | logout route | true | user login sebelum logout | user email |

Console login dan HR login memakai controller yang sama, sehingga keduanya masuk ke observability yang sama.

## Data model

`LoginActivity` menyimpan:

- `user_id`;
- `email`;
- `event`;
- `successful`;
- `ip_address`;
- `user_agent`;
- `device`;
- `browser`;
- `platform`;
- `message`;
- `occurred_at`;
- timestamps.

Relasi:

- `user()` → `User`.

## Recording behavior

`AuthenticatedSessionController` memanggil:

- `recordFailure()` ketika `LoginRequest::authenticate()` melempar `ValidationException`;
- `recordSuccess()` setelah login berhasil dan session diregenerate;
- `recordLogout()` sebelum session logout/invalidate.

`LoginActivityService`:

- memakai `$request->ip()` untuk IP;
- memakai `$request->userAgent()` untuk raw user agent;
- membuat deteksi ringan:
  - device: Mobile/Desktop;
  - browser: Edge/Chrome/Firefox/Safari/Unknown;
  - platform: Windows/macOS/Linux/Android/iOS/Unknown;
- menelan error recording dengan `report($exception)` agar auth flow tidak gagal karena observability write.

## List/filter behavior

Controller `LoginActivityController@index`:

1. authorize `viewAny`;
2. membaca filter:
   - `search`;
   - `event`;
   - `status`;
   - `per_page`;
3. query `LoginActivity` dengan eager load `user:id,name,email`;
4. search mencakup:
   - `email`;
   - `ip_address`;
   - `message`;
   - user `name`;
   - user `email`;
5. filter event exact;
6. filter status:
   - `success` → `successful = true`;
   - `failed` → `successful = false`;
7. sorting `latest('occurred_at')`;
8. pagination memakai `SystemSettingService::resolvePerPage()`;
9. summary cards menghitung total, success, failed, dan today.

Frontend:

- summary cards;
- filter search/status/event;
- tabel login activities;
- detail panel email, IP, browser, platform, device, user agent;
- pagination memakai shared `PaginationBar`.

## Security dan privacy review

Yang sudah baik:

- route view protected oleh permission;
- unauthorized user ditolak;
- halaman login activity read-only;
- password request tidak disimpan;
- logout ikut tercatat;
- failed login mencatat email yang dicoba tanpa password;
- user agent/IP tersimpan untuk investigasi;
- tidak ada mutation route untuk menghapus/ubah login activity.

Temuan/residual risk:

1. Email, IP, dan user agent adalah PII/security metadata.
   - Perlu retention/minimization policy seperti Audit Logs.
2. Failed login menyimpan email dari request.
   - Ini berguna untuk security monitoring, tetapi bisa berisi email typo atau input bukan email valid jika request validation berubah.
3. User agent bisa panjang dan berasal dari client.
   - Saat ini ditampilkan sebagai teks React-escaped, bukan HTML.
4. Tidak ada automatic alert/rate anomaly detector.
   - MVP hanya observability list; alert bisa menjadi roadmap setelah module operational stabil.
5. Login Activities tidak otomatis mengisi `lastLogin` read model User Management.
   - Follow-up dari Checkpoint A bisa dikerjakan setelah semantics final.

## Guide-plan koreksi lanjutan

### LA-01 — Retention dan privacy minimization

**Tujuan:** mengatur umur data login activities dan field yang boleh tampil.

**Rencana:**

1. Samakan dengan audit retention policy.
2. Tentukan apakah IP perlu masking sebagian setelah masa tertentu.
3. Buat command prune/archive jika retention sudah disetujui.

### LA-02 — Last login read model

**Tujuan:** menghubungkan User Management `lastLogin` dengan login activities.

**Rencana:**

1. Tentukan apakah last login memakai event `login` terakhir sukses.
2. Tambahkan query/read model di User Management service.
3. Tambahkan test agar failed login/logout tidak dianggap last login.

### LA-03 — Security signal summary

**Tujuan:** memperkaya observability tanpa membuat alert spekulatif.

**Rencana:**

1. Tambahkan summary failed login 24 jam terakhir per email/IP.
2. Tambahkan filter IP/email exact jika dibutuhkan.
3. Jangan kirim notifikasi otomatis sebelum policy alert disetujui.

## Acceptance review

- [x] Login success/failure tercatat.
- [x] Logout tercatat.
- [x] Password/token tidak pernah dicatat.
- [x] IP/user agent handling terdokumentasi.
- [x] View permission jelas.
- [x] Unauthorized view test tersedia.

## Evidence

```bash
php artisan test --filter=LoginActivity
vendor/bin/pint --test app/Modules/Console/LoginActivities tests/Feature/LoginActivityTest.php resources/js/pages/console/login-activities
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

Hasil terakhir:

- `LoginActivityTest`: 6 tests, 15 assertions, pass.
- Pint targeted: pass.
- TypeScript typecheck: pass.
- Vite build: pass.
- Module contract validation: pass.
- Diff whitespace check: pass.
