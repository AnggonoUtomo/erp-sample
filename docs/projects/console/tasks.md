# Tasks: Console Project

Task ini ditulis seolah-olah Console baru akan dibuat. Karena implementasi sudah ada, eksekusi task berarti: telusuri source aktual, cocokkan dengan specification, revisi docs, tambahkan test/koreksi kecil jika ditemukan gap, lalu berhenti di checkpoint.

## Task 01 — Console shell, auth, dan dashboard baseline ✅

**Tujuan:** menelusuri entry point Console: login, dashboard, layout, sidebar, header, theme, dan shared auth props.

**Files yang ditelusuri/kemungkinan disentuh:**

- `resources/js/pages/console/auth/login.tsx`
- `resources/js/pages/console/dashboard.tsx`
- `resources/js/components/app-sidebar.tsx`
- `resources/js/components/app-menu-header.tsx`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `routes/web.php`
- `tests/Feature/DashboardTest.php`

**Acceptance criteria:**

- [x] Console shell terdokumentasi sebagai base UI pattern.
- [x] Shared auth roles/permissions/super flag jelas.
- [x] Sidebar/header tidak mencampur domain bisnis secara liar.
- [x] Theme/layout behavior terdokumentasi.

**Hasil telusur:** selesai 2026-07-19. Console shell terdokumentasi di [01 — Console Shell Baseline](01-console-shell-baseline.md). `/dashboard` menjadi route Console utama dengan middleware `auth`, guest diarahkan ke `/console/login`, dan HR tetap punya route dashboard/login sendiri dengan permission `hr.view`. Shared Inertia props menjadi kontrak shell untuk branding, localization, pagination, navigation, activity center, flash, auth user, roles, permissions, `super`, dan impersonation. Sidebar mengambil navigation dari `ModuleRegistry`, menerjemahkan menu ke Bahasa Indonesia, mendukung dropdown kategori/nested tooltip/collapsed mode, dan memakai permission filtering sebagai UX convenience; security tetap wajib di backend route/policy. Header menjadi command bar dengan breadcrumbs, activity center, theme toggle, user dropdown, dan search/help placeholder. Dashboard memakai data runtime untuk module count, permission count, role count, dan unread activity, sementara project rows/chart/recent activities masih curated/static.

**Follow-up tercatat:** global search/command palette belum aktif, Help button belum punya aksi, dan sebagian dashboard masih naratif/statis. Ini tidak menghalangi Task 01, tetapi menjadi kandidat koreksi/polish setelah identity/access boundary selesai.

**Cara test:**

```bash
php artisan test --filter=Dashboard
npm run typecheck
npm run build
git diff --check
```

**Dependencies:** none. **Scope:** M.

## Task 02 — Access Control protected authorization boundary ✅

**Tujuan:** menelusuri role/permission management dan memastikan role `super-system` protected.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/AccessControls/*`
- `resources/js/pages/console/access-control/*`
- `database/seeders/ModulePermissionSeeder.php`
- `tests/Feature/AccessControlTest.php`

**Acceptance criteria:**

- [x] Role/permission CRUD terdokumentasi.
- [x] `super-system` visible hanya untuk akun super-system.
- [x] `super-system` tidak bisa dibuat, rename, delete, atau sync permission via UI biasa.
- [x] Permission module panel dan grouping terdokumentasi.

**Hasil telusur:** selesai 2026-07-19. Access Control boundary terdokumentasi di [02 — Access Control Boundary](02-access-control-boundary.md). Module mengekspor route, permission, dan navigation melalui contract module. Semua route memakai `auth`, lalu action penting dilindungi middleware `can` berbasis `AccessControlPolicy`. `roles.manage` menjadi manage permission tertinggi, sedangkan `access-control.view/create/update/delete` menjadi permission granular role CRUD. Role `super-system` disembunyikan dari actor non-super-system di `AccessControlService::getPageData()`, tetap terlihat sebagai protected role untuk akun super-system, dan tidak dapat dibuat/update/delete/sync permission lewat UI biasa karena ditolak policy/request/controller. Frontend sudah memakai disabled state, protected badge, dan keyboard shortcut guard sebagai UX layer; security boundary tetap backend.

**Follow-up tercatat:** label breadcrumb/page masih “Access Control” sementara sidebar “Kontrol Akses”; permission creation/deletion sengaja lebih ketat via `roles.manage`; Add Role dialog belum memberi hint eksplisit bahwa `super-system` tidak boleh dibuat; label permission group masih headline otomatis dari prefix permission.

**Cara test:**

```bash
php artisan test --filter=AccessControl
php artisan module:validate
npm run typecheck
git diff --check
```

**Dependencies:** Task 01. **Scope:** M.

## Task 03 — User Management lifecycle ✅

**Tujuan:** menelusuri user create/update/archive/restore/force-delete, avatar, reset/activation link, role/direct permission, dan impersonation.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/UserManagements/*`
- `resources/js/pages/console/users/*`
- `app/Models/User.php`
- `database/seeders/UserSeeder.php`
- `tests/Feature/UserManagementTest.php`
- `tests/Feature/UserImpersonationTest.php`

**Acceptance criteria:**

- [x] User lifecycle dan soft delete policy jelas.
- [x] Role protected tidak assignable dari payload user management.
- [x] Avatar upload/crop/remove terdokumentasi.
- [x] Password reset/activation link behavior jelas untuk email/log local.
- [x] Impersonation menolak target super-system untuk non-super-system.

**Hasil telusur:** selesai 2026-07-19. User Management lifecycle terdokumentasi di [03 — User Management Lifecycle](03-user-management-lifecycle.md). Module bergantung ke `Console.AccessControls`, mengekspor route/permission/navigation, dan mengelola akun login, avatar, role/direct permission, activation/reset link, archive/restore/force-delete, serta impersonation. Semua route wajib `auth`; create/update memakai FormRequest authorization, sedangkan delete/restore/force-delete memakai `UserPolicy`. Role `super-system` disembunyikan dari actor non-super-system, tidak bisa diassign lewat User Management, tetap dipertahankan saat update user super-system, dan tidak bisa menjadi target impersonation. Password tidak pernah diatur manual dari User Management; create/update hanya dapat memicu activation/reset link melalui job mail jika System Settings mengizinkan. Profile settings `/settings/profile` dicatat sebagai self-service lifecycle terkait avatar dan delete account visibility.

**Follow-up tercatat:** validasi avatar User Management belum memakai MIME allowlist eksplisit seperti profile settings; label UI masih campuran “User/User Management/Manajemen User”; `lastLogin` read model masih `null`; audit impersonation menyimpan email actor/target sebagai identifier audit; stop impersonation route sengaja berbasis session, bukan permission.

**Cara test:**

```bash
php artisan test --filter="UserManagement|UserImpersonation|ProfileUpdate|PasswordUpdate"
vendor/bin/pint --test app/Modules/Console/UserManagements app/Models/User.php tests/Feature/UserManagementTest.php tests/Feature/UserImpersonationTest.php
git diff --check
```

**Dependencies:** Task 02. **Scope:** M.

## Checkpoint A — Identity and access boundary ✅

- [x] Task 01–03 selesai.
- [x] Protected role policy/visibility sudah terdokumentasi.
- [x] Denial matrix users/access-control hijau.
- [x] Tidak ada secret/password/token masuk response/audit.

**Hasil checkpoint:** selesai 2026-07-19. Ringkasan gabungan tersedia di [Checkpoint A — Identity and Access Boundary](checkpoint-a-identity-access-boundary.md). Evidence gabungan `Dashboard|AccessControl|UserManagement|UserImpersonation`, `module:validate`, `typecheck`, dan `build` hijau. Protected `super-system`, user lifecycle, role/permission mutation denial, dan impersonation boundary sudah cukup kuat untuk lanjut ke System Settings.

**Follow-up tercatat:** samakan MIME allowlist avatar User Management dengan profile settings; polish label Bahasa Indonesia; hubungkan `lastLogin` ke Login Activities saat Task 08; formalkan audit retention/PII minimization saat Task 07; aktifkan global search/help pada polish Console berikutnya.

**Evidence:**

```bash
php artisan test --filter="Dashboard|AccessControl|UserManagement|UserImpersonation"
php artisan module:validate
npm run typecheck
npm run build
git diff --check
```

## Task 04 — System Settings configuration boundary ✅

**Tujuan:** menelusuri konfigurasi runtime dan memastikan secret/setting sensitif aman.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/SystemSettings/*`
- `resources/js/pages/console/system-settings/*`
- `tests/Feature/SystemSettingTest.php`
- `tests/Feature/Settings/ProfileUpdateTest.php`

**Acceptance criteria:**

- [x] Branding/localization/pagination/password/security/email/map/maintenance terdokumentasi.
- [x] Secret email/map tidak dikirim plaintext ke frontend.
  - Email password sudah aman/masked.
  - Map API key sudah encrypted at rest dan masked pada System Settings props/audit.
  - Maintenance secret/bypass URL sudah encrypted/masked pada System Settings props/audit.
- [x] Delete account visibility setting terdokumentasi.
- [x] Email automation local/log behavior terdokumentasi.

**Hasil telusur dan hardening:** selesai 2026-07-19. Dokumentasi tersedia di [04 — System Settings Boundary](04-system-settings-boundary.md). System Settings sudah punya route `auth`, policy `system-settings.view/update`, request authorization, DTO, service, audit, dan panel frontend untuk email, branding, localization, pagination, security policy, password policy, maintenance, map, health, dan environment. Hardening kecil sudah diterapkan: `google_maps_api_key` dan maintenance `secret` disimpan encrypted, tidak dikirim balik plaintext pada System Settings props, update kosong mempertahankan secret lama, dan audit hanya mencatat status configured tanpa nilai secret.

**Cara test:**

```bash
php artisan test --filter="SystemSetting|ProfileUpdate"
vendor/bin/pint --test app/Modules/Console/SystemSettings tests/Feature/SystemSettingTest.php
npm run typecheck
npm run build
git diff --check
```

**Dependencies:** Checkpoint A. **Scope:** M.

## Task 05 — Notification Templates lifecycle ✅

**Tujuan:** menelusuri template notification/email dan preview/update behavior.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/NotificationTemplates/*`
- `resources/js/pages/console/notification-templates/*`
- `tests/Feature/NotificationTemplateTest.php`

**Acceptance criteria:**

- [x] Template key/channel/body/subject terdokumentasi.
- [x] Update dibatasi permission.
- [x] Preview tidak mengirim secret/PII sensitif yang salah.
- [x] Template default dari seeder/module permission jelas.

**Hasil telusur:** selesai 2026-07-19. Dokumentasi tersedia di [05 — Notification Templates Lifecycle](05-notification-templates-lifecycle.md). Module mengekspor route, permission, navigation, policy, FormRequest, service default template, dan frontend editor/list. Route view/update wajib `auth`; view dikontrol `notification-templates.view`, sedangkan update dikontrol `notification-templates.update`. Default template aktif saat ini adalah `user.activation`, `user.credential`, dan `smtp.test`. Halaman template tidak mengirim email dan belum punya executable preview route; karena itu tidak ada jalur preview yang mengirim secret/PII. Gap yang dicatat: legacy `user.credential`, `SendUserCredentialNotificationJob`, dan `UserCredentialNotification` masih membawa konsep plain password meski tidak ditemukan caller aktif pada flow User Management saat ini.

**Guide-plan koreksi:** depresiasi/guard legacy credential password notification, tambahkan safe preview endpoint jika dibutuhkan, dan formalkan policy audit body agar isi template tidak menjadi tempat secret nyata.

**Cara test:**

```bash
php artisan test --filter=NotificationTemplate
vendor/bin/pint --test app/Modules/Console/NotificationTemplates tests/Feature/NotificationTemplateTest.php
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

**Dependencies:** Task 04. **Scope:** S.

## Checkpoint B — Configuration and notification boundary ✅

- [x] Task 04–05 selesai.
- [x] Secret masking/encryption policy jelas.
- [x] Email/log delivery mode jelas.
- [x] Mutation settings/templates permission-gated.

**Hasil checkpoint:** selesai 2026-07-19. Ringkasan gabungan tersedia di [Checkpoint B — Configuration and Notification Boundary](checkpoint-b-configuration-notification-boundary.md). Evidence gabungan `SystemSetting|NotificationTemplate|ProfileUpdate`, targeted Pint, `module:validate`, `typecheck`, `build`, dan `git diff --check` hijau. System Settings sudah aman untuk secret email/map/maintenance pada props dan audit, email delivery mode local/log jelas, delete account visibility ditegakkan, dan Notification Templates sudah permission-gated. Tidak ada blocker untuk lanjut ke Activity Center.

**Follow-up tercatat:** depresiasi/guard legacy credential password notification, safe preview endpoint jika dibutuhkan, audit body policy untuk template, enforcement tambahan security policy seperti single session/email verification, dan copy inactive template agar selaras dengan behavior service.

## Task 06 — Activity Center read model ✅

**Tujuan:** menelusuri activity center sebagai read model aktivitas user.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/ActivityCenters/*`
- `resources/js/components/activity-center-dropdown.tsx`
- `tests/Feature/ActivityCenterTest.php`

**Acceptance criteria:**

- [x] Activity center tidak menjadi source mutation bisnis.
- [x] Read/mark behavior jelas.
- [x] Tidak ada navigation menu wajib jika hanya dropdown.

**Hasil telusur:** selesai 2026-07-19. Dokumentasi tersedia di [06 — Activity Center Read Model](06-activity-center-read-model.md). Module `Console.ActivityCenters` hanya mengekspor route dan permission, tanpa navigation menu, karena tampil sebagai dropdown header. Shared Inertia props `activity_center` membaca `AuditLog` terbaru lewat `ActivityCenterService::summaryFor()` jika user punya `activity-center.view`; user tanpa permission mendapat fallback kosong. Mutation satu-satunya adalah `POST activity-center/read`, yang hanya mengisi `users.activity_center_read_at` milik user login sebagai read marker. Module ini tidak membuat/mengubah/menghapus audit log atau data bisnis.

**Follow-up tercatat:** Activity Center menampilkan `actor.email` dari audit log dan mewarisi kualitas masking Audit Logs; PII minimization dan audit payload policy dibahas di Task 07.

**Cara test:**

```bash
php artisan test --filter=ActivityCenter
vendor/bin/pint --test app/Modules/Console/ActivityCenters tests/Feature/ActivityCenterTest.php resources/js/components/activity-center-dropdown.tsx
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

**Dependencies:** Checkpoint B. **Scope:** S.

## Task 07 — Audit Logs immutable boundary ✅

**Tujuan:** menelusuri audit log sebagai histori immutable mutation penting.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/AuditLogs/*`
- `resources/js/pages/console/audit-logs/*`
- `tests/Feature/AuditLogTest.php`

**Acceptance criteria:**

- [x] Audit log read-only untuk user.
- [x] Audit service tidak menyimpan password/token/secret.
- [x] Filter/list behavior terdokumentasi.
- [x] Retention/archival gap dicatat jika belum ada.

**Hasil telusur dan hardening:** selesai 2026-07-19. Dokumentasi tersedia di [07 — Audit Logs Immutable Boundary](07-audit-logs-immutable-boundary.md). Module `Console.AuditLogs` hanya memiliki route user-facing `GET /audit-logs` dengan policy `audit-logs.view`; tidak ada route create/update/delete untuk user. List mendukung search, module filter, event filter, pagination dari System Settings, dan detail old/new values. Hardening kecil diterapkan pada `AuditLogService`: sanitizer `old_values`/`new_values` sekarang recursive dan me-redact key sensitif seperti password, token, secret, api_key, dan apikey menjadi `[redacted]`.

**Follow-up tercatat:** retention/archival policy belum ada, actor email/IP/user agent adalah PII yang perlu minimization policy, description caller belum disanitasi otomatis, dan tamper-evident audit chain belum menjadi scope MVP.

**Cara test:**

```bash
php artisan test --filter=AuditLog
vendor/bin/pint --test app/Modules/Console/AuditLogs tests/Feature/AuditLogTest.php resources/js/pages/console/audit-logs/index.tsx
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

**Dependencies:** Task 06. **Scope:** S.

## Task 08 — Login Activities security observability ✅

**Tujuan:** menelusuri log login sukses/gagal tanpa menyimpan credential.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/LoginActivities/*`
- `resources/js/pages/console/login-activities/*`
- `tests/Feature/LoginActivityTest.php`

**Acceptance criteria:**

- [x] Login success/failure tercatat.
- [x] Password/token tidak pernah dicatat.
- [x] IP/user agent handling terdokumentasi.
- [x] View permission jelas.

**Hasil telusur dan coverage:** selesai 2026-07-19. Dokumentasi tersedia di [08 — Login Activities Security Observability](08-login-activities-security-observability.md). Module `Console.LoginActivities` hanya memiliki route user-facing `GET /login-activities` dengan policy `login-activities.view`; tidak ada route mutation untuk user. Recording dilakukan internal dari `AuthenticatedSessionController` untuk `login`, `login_failed`, dan `logout`. Data yang disimpan adalah metadata security seperti email, event, success flag, IP, user agent, device/browser/platform, message, dan waktu kejadian; password/token tidak disimpan. Test ditambah untuk unauthorized view, logout recorded, dan submitted password tidak masuk message/user_agent.

**Follow-up tercatat:** retention/privacy policy untuk email/IP/user agent, `lastLogin` read model User Management dari event login sukses terakhir, dan security signal summary/alert sebagai roadmap setelah policy disetujui.

**Cara test:**

```bash
php artisan test --filter=LoginActivity
vendor/bin/pint --test app/Modules/Console/LoginActivities tests/Feature/LoginActivityTest.php resources/js/pages/console/login-activities
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

**Dependencies:** Task 07. **Scope:** S.

## Checkpoint C — Observability boundary ✅

- [x] Task 06–08 selesai.
- [x] Activity/audit/login logs aman dari secret leakage.
- [x] Read-only observability route terlindungi permission.

**Hasil checkpoint:** selesai 2026-07-19. Ringkasan gabungan tersedia di [Checkpoint C — Observability Boundary](checkpoint-c-observability-boundary.md). Evidence gabungan `ActivityCenter|AuditLog|LoginActivity`, targeted Pint, `module:validate`, `typecheck`, `build`, dan `git diff --check` hijau. Activity Center hanya read model dari Audit Logs plus marker baca user sendiri; Audit Logs read-only dari sisi user dan sanitizer old/new values sudah recursive; Login Activities mencatat login sukses/gagal/logout tanpa password/token.

**Follow-up tercatat:** retention/archive policy, PII minimization untuk email/IP/user-agent, audit description safety, User Management `lastLogin` read model dari login sukses terakhir, dan tamper-evident audit chain sebagai roadmap compliance.

## Task 09 — Queue Monitor runtime control ✅

**Tujuan:** menelusuri queue monitor, failed job listing, retry, forget, dan flush.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/QueueMonitors/*`
- `resources/js/pages/console/queue-monitor/*`
- `tests/Feature/QueueMonitorTest.php`

**Acceptance criteria:**

- [x] View dan manage permission terpisah.
- [x] Retry/forget/flush denial matrix hijau.
- [x] Payload failed job tidak mengekspos secret berlebih.

**Hasil telusur dan hardening:** selesai 2026-07-19. Dokumentasi tersedia di [09 — Queue Monitor Runtime Control](09-queue-monitor-runtime-control.md). Module `Console.QueueMonitors` memisahkan `queue-monitor.view` untuk halaman index dan `queue-monitor.manage` untuk retry/forget/flush failed jobs. Controller melakukan authorization langsung pada setiap action; frontend hanya memberi UX disabled state. Service tidak mengirim raw payload job ke UI, hanya job name dan exception summary. Hardening kecil diterapkan: exception summary me-redact pola `password`, `token`, `secret`, `api_key`, `api-key`, dan `apikey` sebelum dikirim ke frontend.

**Follow-up tercatat:** audit runtime queue actions, dialog safety untuk retry/flush, compatibility note untuk queue driver non-database, dan reminder bahwa retry job tidak boleh menjadi bypass domain authorization.

**Cara test:**

```bash
php artisan test --filter=QueueMonitor
vendor/bin/pint --test app/Modules/Console/QueueMonitors tests/Feature/QueueMonitorTest.php resources/js/pages/console/queue-monitor
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

**Dependencies:** Checkpoint C. **Scope:** M.

## Task 10 — Scheduler Monitor runtime control ✅

**Tujuan:** menelusuri scheduler monitor, task list, dan controlled run due task.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/SchedulerMonitors/*`
- `resources/js/pages/console/scheduler-monitor/*`
- `tests/Feature/SchedulerMonitorTest.php`

**Acceptance criteria:**

- [x] Scheduled task list read-only tersedia.
- [x] Run due task hanya untuk manage permission.
- [x] Timezone/date semantics terdokumentasi.

**Hasil telusur dan hardening:** selesai 2026-07-19. Dokumentasi tersedia di [10 — Scheduler Monitor Runtime Control](10-scheduler-monitor-runtime-control.md). Module `Console.SchedulerMonitors` memisahkan `scheduler-monitor.view` untuk halaman index dan `scheduler-monitor.manage` untuk aksi `Run Due Tasks`. List scheduled tasks read-only dari `schedule:list --json --next --timezone=<app timezone>`. Aksi manage hanya menjalankan `schedule:run`, bukan arbitrary command, dan output Artisan sekarang di-redact untuk pola `password`, `token`, `secret`, `api_key`, `api-key`, dan `apikey` sebelum tampil sebagai flash message. Timezone/date semantics, heartbeat cache status, dan risiko runtime side effect sudah dicatat.

**Follow-up tercatat:** audit runtime scheduler actions, dialog operator yang lebih informatif, warning UI untuk heartbeat `stale/down/never`, dan reminder bahwa command/job scheduler harus tetap idempotent serta enforce invariant domain masing-masing.

**Cara test:**

```bash
php artisan test --filter=SchedulerMonitor
vendor/bin/pint --test app/Modules/Console/SchedulerMonitors tests/Feature/SchedulerMonitorTest.php resources/js/pages/console/scheduler-monitor
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

**Dependencies:** Task 09. **Scope:** M.

## Checkpoint D — Runtime operation boundary ✅

- [x] Task 09–10 selesai.
- [x] Queue/scheduler manage action permission-gated.
- [x] Runtime operations tidak menjadi bypass business authorization.

**Hasil checkpoint:** selesai 2026-07-19. Ringkasan gabungan tersedia di [Checkpoint D — Runtime Operation Boundary](checkpoint-d-runtime-operation-boundary.md). Evidence gabungan `QueueMonitor|SchedulerMonitor`, targeted Pint masing-masing runtime module, `module:validate`, `typecheck`, `build`, dan `git diff --check` hijau. Queue Monitor dan Scheduler Monitor sudah memisahkan view/manage permission, menolak user tanpa permission, menghindari raw payload/secret leakage dasar, dan didokumentasikan sebagai operator tool yang tidak boleh menggantikan authorization/invariant di job/command domain.

**Follow-up tercatat:** audit runtime actions, operator safety dialog, queue driver compatibility warning, scheduler heartbeat troubleshooting UI, dan domain idempotency contract untuk job/command penting.

## Task 11 — Backup Restore signed recovery boundary ✅

**Tujuan:** menelusuri backup/restore database, storage, settings, signature, validator, SQL dump executor, ZIP builder/restorer.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/BackupRestores/*`
- `resources/js/pages/console/backup-restore/*`
- `tests/Feature/BackupRestoreTest.php`
- `docs/reviews/2026-07-11-project-baseline/09-backup-signature-runbook.md`

**Acceptance criteria:**

- [x] Backup format aktif terdokumentasi.
- [x] Signature/authenticity lintas environment jelas.
- [x] Restore unsafe/legacy ditolak.
- [x] DMS private storage coverage jelas.
- [x] Full restore memakai validation/dry-run yang eksplisit.

**Hasil telusur dan hardening:** selesai 2026-07-19. Dokumentasi tersedia di [11 — Backup Restore Signed Recovery Boundary](11-backup-restore-signed-recovery-boundary.md). Module `Console.BackupRestores` memisahkan settings backup JSON dan full signed ZIP v3. Full backup aktif memakai schema `laravel12-starterkit.full-backup`, version `3`, HMAC-SHA256 signature dengan `BACKUP_SIGNATURE_KEY`, exact SHA-256 entry list, `database.sql`, `storage_public/*`, dan `storage_dms_private/*` untuk private Document Management. Full restore hanya menerima signed `.zip`, menolak raw SQL/legacy/unsafe archive, melewati archive/signature/checksum validation sebelum write, dan sekarang punya `dry_run` eksplisit yang default aktif di UI untuk memvalidasi tanpa menulis database/storage.

**Follow-up tercatat:** restore drill SOP di UI, multi-key verification untuk rotasi, asymmetric signature evaluation, restore staging directory untuk filesystem, dan backup size/runtime observability.

**Cara test:**

```bash
php artisan test --filter=BackupRestore
vendor/bin/pint --test app/Modules/Console/BackupRestores tests/Feature/BackupRestoreTest.php
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

**Dependencies:** Checkpoint D. **Scope:** M.

## Checkpoint E — Recovery boundary

- [ ] Task 11 selesai.
- [ ] Runbook backup sesuai behavior aktual.
- [ ] Recovery path tidak memberi rasa aman palsu.

## Task 12 — Console module guide dan roadmap

**Tujuan:** membuat dokumen lanjutan setelah penelusuran awal agar Console punya guide seperti HR.

**Files yang disentuh:**

- `docs/projects/console/module-guide.md`
- `docs/projects/console/roadmap.md`
- `docs/projects/console/README.md`
- `docs/README.md` bila perlu cross-link

**Acceptance criteria:**

- [ ] Guide mencatat aturan module Console.
- [ ] Roadmap mencatat status module-by-module.
- [ ] Cross-link ke docs architecture/reviews tersedia.

**Cara test:**

```bash
git diff --check
```

**Dependencies:** Checkpoint E. **Scope:** S.

## Final quality checkpoint

- [ ] Semua task penelusuran Console selesai.
- [ ] Gap correction guide tersedia.
- [ ] Relevant tests hijau.
- [ ] README/spec/plan/tasks/ADR sesuai implementasi aktual.

**Evidence final:**

```bash
vendor/bin/pint --test
php artisan module:validate
npm run format:check
npm run lint:check
npm run typecheck
npm run build
php artisan test
git diff --check
```
