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

## Task 05 — Notification Templates lifecycle

**Tujuan:** menelusuri template notification/email dan preview/update behavior.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/NotificationTemplates/*`
- `resources/js/pages/console/notification-templates/*`
- `tests/Feature/NotificationTemplateTest.php`

**Acceptance criteria:**

- [ ] Template key/channel/body/subject terdokumentasi.
- [ ] Update dibatasi permission.
- [ ] Preview tidak mengirim secret/PII sensitif yang salah.
- [ ] Template default dari seeder/module permission jelas.

**Cara test:**

```bash
php artisan test --filter=NotificationTemplate
git diff --check
```

**Dependencies:** Task 04. **Scope:** S.

## Checkpoint B — Configuration and notification boundary

- [ ] Task 04–05 selesai.
- [ ] Secret masking/encryption policy jelas.
- [ ] Email/log delivery mode jelas.
- [ ] Mutation settings/templates permission-gated.

## Task 06 — Activity Center read model

**Tujuan:** menelusuri activity center sebagai read model aktivitas user.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/ActivityCenters/*`
- `resources/js/components/activity-center-dropdown.tsx`
- `tests/Feature/ActivityCenterTest.php`

**Acceptance criteria:**

- [ ] Activity center tidak menjadi source mutation bisnis.
- [ ] Read/mark behavior jelas.
- [ ] Tidak ada navigation menu wajib jika hanya dropdown.

**Cara test:**

```bash
php artisan test --filter=ActivityCenter
git diff --check
```

**Dependencies:** Checkpoint B. **Scope:** S.

## Task 07 — Audit Logs immutable boundary

**Tujuan:** menelusuri audit log sebagai histori immutable mutation penting.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/AuditLogs/*`
- `resources/js/pages/console/audit-logs/*`
- `tests/Feature/AuditLogTest.php`

**Acceptance criteria:**

- [ ] Audit log read-only untuk user.
- [ ] Audit service tidak menyimpan password/token/secret.
- [ ] Filter/list behavior terdokumentasi.
- [ ] Retention/archival gap dicatat jika belum ada.

**Cara test:**

```bash
php artisan test --filter=AuditLog
git diff --check
```

**Dependencies:** Task 06. **Scope:** S.

## Task 08 — Login Activities security observability

**Tujuan:** menelusuri log login sukses/gagal tanpa menyimpan credential.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/LoginActivities/*`
- `resources/js/pages/console/login-activities/*`
- `tests/Feature/LoginActivityTest.php`

**Acceptance criteria:**

- [ ] Login success/failure tercatat.
- [ ] Password/token tidak pernah dicatat.
- [ ] IP/user agent handling terdokumentasi.
- [ ] View permission jelas.

**Cara test:**

```bash
php artisan test --filter=LoginActivity
git diff --check
```

**Dependencies:** Task 07. **Scope:** S.

## Checkpoint C — Observability boundary

- [ ] Task 06–08 selesai.
- [ ] Activity/audit/login logs aman dari secret leakage.
- [ ] Read-only observability route terlindungi permission.

## Task 09 — Queue Monitor runtime control

**Tujuan:** menelusuri queue monitor, failed job listing, retry, forget, dan flush.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/QueueMonitors/*`
- `resources/js/pages/console/queue-monitor/*`
- `tests/Feature/QueueMonitorTest.php`

**Acceptance criteria:**

- [ ] View dan manage permission terpisah.
- [ ] Retry/forget/flush denial matrix hijau.
- [ ] Payload failed job tidak mengekspos secret berlebih.

**Cara test:**

```bash
php artisan test --filter=QueueMonitor
git diff --check
```

**Dependencies:** Checkpoint C. **Scope:** M.

## Task 10 — Scheduler Monitor runtime control

**Tujuan:** menelusuri scheduler monitor, task list, dan controlled run due task.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/SchedulerMonitors/*`
- `resources/js/pages/console/scheduler-monitor/*`
- `tests/Feature/SchedulerMonitorTest.php`

**Acceptance criteria:**

- [ ] Scheduled task list read-only tersedia.
- [ ] Run due task hanya untuk manage permission.
- [ ] Timezone/date semantics terdokumentasi.

**Cara test:**

```bash
php artisan test --filter=SchedulerMonitor
git diff --check
```

**Dependencies:** Task 09. **Scope:** M.

## Checkpoint D — Runtime operation boundary

- [ ] Task 09–10 selesai.
- [ ] Queue/scheduler manage action permission-gated.
- [ ] Runtime operations tidak menjadi bypass business authorization.

## Task 11 — Backup Restore signed recovery boundary

**Tujuan:** menelusuri backup/restore database, storage, settings, signature, validator, SQL dump executor, ZIP builder/restorer.

**Files yang ditelusuri/kemungkinan disentuh:**

- `app/Modules/Console/BackupRestores/*`
- `resources/js/pages/console/backup-restore/*`
- `tests/Feature/BackupRestoreTest.php`
- `docs/reviews/2026-07-11-project-baseline/09-backup-signature-runbook.md`

**Acceptance criteria:**

- [ ] Backup format aktif terdokumentasi.
- [ ] Signature/authenticity lintas environment jelas.
- [ ] Restore unsafe/legacy ditolak.
- [ ] DMS private storage coverage jelas.
- [ ] Full restore memakai validation/dry-run yang eksplisit.

**Cara test:**

```bash
php artisan test --filter=BackupRestore
vendor/bin/pint --test app/Modules/Console/BackupRestores tests/Feature/BackupRestoreTest.php
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
