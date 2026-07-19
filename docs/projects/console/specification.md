# Specification: Console Project

## 1. Objective

Membuat dokumentasi source-of-truth untuk project `Console` sebagai fondasi operasional aplikasi. Dokumentasi ini memetakan apa yang seharusnya dibangun jika Console dimulai dari nol: identity, authorization, settings, observability, notification, queue/scheduler monitor, backup/restore, dan UI shell.

Keberhasilan dokumen ini berarti developer dapat menelusuri Console module-by-module, memahami dependensi antar module, menemukan gap yang memerlukan koreksi, dan menjalankan task incremental tanpa mengubah behavior di luar scope.

## 2. Assumptions

1. Console adalah project default di `app/Modules/Console`.
2. Route Console tidak memakai prefix `/console`; contoh route adalah `/users`, `/access-control`, `/system-settings`.
3. Authentication memakai session Laravel dan Inertia.
4. Authorization memakai Spatie Permission dengan role protected `super-system`.
5. Console User adalah identity boundary yang boleh direferensikan project lain, tetapi data employee/profile bisnis tetap berada di project bisnis seperti HR.
6. Settings, backup, audit, queue, scheduler, dan notification adalah operational concern global.
7. Dokumentasi ini tidak memerintahkan rebuild; koreksi dilakukan setelah evaluasi module-by-module.

## 3. Scope MVP

### 3.1 Identity dan access control

MVP mencakup:

- `super-system` sebagai role tertinggi dan protected.
- user management untuk create/update/archive/restore/force-delete user.
- avatar profile/user management.
- password reset/activation link via email/log.
- impersonation terbatas.
- role/permission management.
- permission module panel untuk memberi akses project/module.

### 3.2 System settings

MVP mencakup:

- branding aplikasi;
- localization/timezone;
- pagination default;
- password policy;
- security policy termasuk visibility delete account;
- email configuration dan test email;
- maintenance mode;
- map configuration;
- health/environment panel.

Secret seperti mail password/API key tidak boleh dikirim balik ke frontend dalam bentuk plaintext.

### 3.3 Notification dan observability

MVP mencakup:

- notification templates;
- activity center;
- audit logs;
- login activities.

Audit/log tidak boleh membawa password, token, secret, credential, full signed URL, atau payload sensitif yang tidak perlu.

### 3.4 Runtime operations

MVP mencakup:

- queue monitor untuk failed jobs;
- retry/forget/flush dengan permission manage;
- scheduler monitor untuk daftar task;
- run due task dengan permission manage.

### 3.5 Backup dan restore

MVP mencakup:

- backup settings;
- database dump;
- public storage;
- private Document Management storage bila tersedia;
- signed full-backup;
- verification/dry-run;
- restore hardening;
- authenticity/signature lintas environment.

## 4. Non-scope

Console tidak mencakup:

- data bisnis HR/Payroll/CRM/Accounting;
- payroll calculation;
- attendance/leave workflow;
- employee lifecycle;
- public API external admin;
- multi-tenant admin console;
- SSO/OAuth provider baru;
- approval workflow role/permission kompleks;
- data warehouse;
- malware scanner;
- queue/outbox integration bus.

Jika salah satu hal di atas diperlukan, buat project/module atau ADR terpisah.

## 5. Project structure

```txt
app/Modules/Console/
  AccessControls/
  ActivityCenters/
  AuditLogs/
  BackupRestores/
  LoginActivities/
  NotificationTemplates/
  QueueMonitors/
  SchedulerMonitors/
  SystemSettings/
  UserManagements/

resources/js/pages/console/
  access-control/
  audit-logs/
  auth/
  backup-restore/
  login-activities/
  notification-templates/
  queue-monitor/
  scheduler-monitor/
  system-settings/
  users/
  dashboard.tsx
```

Setiap module Console idealnya memiliki:

```txt
module.php
permissions.php
navigation.php (jika punya menu)
routes.php (jika punya route user/CLI HTTP)
Providers/*ServiceProvider.php
Policies/*
Http/Controllers/*
Http/Requests/*
Services/*
DTO/*
Support/*
Transactions/* (untuk mutation atomic)
tests/Feature/*Test.php
```

Pengecualian yang valid:

- `ActivityCenters` boleh tidak punya `navigation.php` jika hanya muncul sebagai dropdown/header.
- Monitor read-only boleh tidak punya model bila membaca runtime Laravel.
- Contract/operational module boleh tidak punya migration jika tidak menyimpan data sendiri.

## 6. Backend rules

- Controller hanya orkestrasi request/response.
- FormRequest memvalidasi input.
- DTO menormalisasi payload.
- Service menjalankan use case.
- Transaction menjaga mutation atomic.
- Policy/middleware `can` melindungi route mutasi.
- Audit untuk mutation penting harus berada dalam transaction atau rollback-safe.
- Permission didefinisikan di `permissions.php` dan disinkronkan lewat seeder/registry.
- Role `super-system` protected: visible hanya untuk akun super-system, tidak bisa dibuat/assign/update/delete dari UI biasa.

## 7. Frontend rules

- `index.tsx` menjadi page composer, bukan semua logic.
- Komponen besar ditempatkan di `*-components/`.
- Type lokal ditempatkan di `types.ts`.
- UI memakai shadcn-style component existing.
- Sidebar/header/layout Console menjadi canonical pattern project.
- State destructive action memakai dialog konfirmasi.
- Mutasi Inertia menampilkan loading/disabled state.
- Secret/config sensitive tidak ditampilkan mentah.

## 8. Command design

Command/quality gate Console:

```bash
php artisan module:validate
php artisan test --filter=AccessControl
php artisan test --filter=UserManagement
php artisan test --filter=SystemSetting
php artisan test --filter=NotificationTemplate
php artisan test --filter=AuditLog
php artisan test --filter=LoginActivity
php artisan test --filter=QueueMonitor
php artisan test --filter=SchedulerMonitor
php artisan test --filter=BackupRestore
php artisan test --filter=ActivityCenter
vendor/bin/pint --test app/Modules/Console tests/Feature/*Console*
npm run format:check
npm run lint:check
npm run typecheck
npm run build
git diff --check
```

Untuk penelusuran module-by-module, jalankan test terarah sesuai module sebelum full test.

## 9. Authorization matrix MVP

| Area | View permission | Mutation/manage permission | Catatan |
|---|---|---|---|
| Access Control | `access-control.view` atau `roles.manage` | `roles.manage`, `access-control.create/update/delete` | `super-system` protected; permission CRUD hanya via `roles.manage` |
| Users | `users.view` | `users.create/update/delete/restore/force-delete/impersonate` | Role protected tidak assignable |
| System Settings | `system-settings.view` | `system-settings.update` | Secret masked |
| Notification Templates | `notification-templates.view` | `notification-templates.update` | Preview aman |
| Audit Logs | `audit-logs.view` | none | Immutable read-only |
| Login Activities | `login-activities.view` | none | Password/token tidak dicatat |
| Queue Monitor | `queue-monitor.view` | `queue-monitor.manage` | Retry/forget/flush controlled |
| Scheduler Monitor | `scheduler-monitor.view` | `scheduler-monitor.manage` | Run due task controlled |
| Backup Restore | `backup-restore.view` | `backup-restore.create/restore/full.restore` | Signed/dry-run |

## 10. Acceptance criteria

- Semua module Console punya contract file yang sesuai kebutuhan.
- Permission/navigation/route discovery valid melalui `module:validate`.
- Role `super-system` protected dan hanya visible untuk akun super-system.
- Semua route mutasi punya policy/permission denial test.
- Settings tidak membocorkan secret.
- Audit/log tidak menyimpan password/token/secret.
- Backup signed dapat diverifikasi sebelum restore.
- Queue/scheduler destructive controls hanya untuk role berizin.
- Frontend mengikuti pola komponen yang maintainable.
- Dokumen module-by-module siap dipakai untuk koreksi incremental.

## 11. Test plan

Minimum checkpoint:

```bash
vendor/bin/pint --test app/Modules/Console tests/Feature/AccessControlTest.php tests/Feature/UserManagementTest.php tests/Feature/SystemSettingTest.php tests/Feature/BackupRestoreTest.php
php artisan module:validate
php artisan test --filter="AccessControl|UserManagement|SystemSetting|NotificationTemplate|AuditLog|LoginActivity|QueueMonitor|SchedulerMonitor|BackupRestore|ActivityCenter"
npm run format:check
npm run lint:check
npm run typecheck
npm run build
git diff --check
```

Full checkpoint:

```bash
vendor/bin/pint --test
php artisan module:validate
npm run quality:check
php artisan test
git diff --check
```

## 12. Open questions untuk evaluasi berikutnya

1. Apakah Console perlu `docs/projects/console/<module>/` per submodule seperti HR?
2. Apakah Access Control perlu read-only export/report permission matrix?
3. Apakah System Settings perlu ADR khusus untuk secret masking/encryption?
4. Apakah Audit Logs perlu retention/archival policy?
5. Apakah Backup Restore perlu scheduled backup MVP atau tetap manual?
