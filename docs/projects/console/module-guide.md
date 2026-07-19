# Panduan Module Project Console

Dokumen ini adalah panduan kerja khusus untuk project `Console`. Roadmap besarnya ada di [roadmap.md](roadmap.md), sedangkan dokumen ini fokus pada aturan teknis saat membuat, menelusuri, dan mengembangkan module Console.

Console adalah fondasi operasional aplikasi: identity, authorization, konfigurasi sistem, observability, runtime monitor, dan recovery. Console bukan tempat proses bisnis HR, Payroll, Attendance, CRM, Accounting, atau Document Management.

## Status saat ini

Project `Console` sudah memiliki module:

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

Hasil telusur module-by-module sudah tersedia di dokumen Task 01 sampai Checkpoint E pada [README Console](README.md).

## Prinsip utama Console

- Console adalah operational foundation.
- Console User adalah identity/account boundary.
- Employee, customer, vendor, transaction, dan dokumen bisnis tetap berada di project pemiliknya.
- Authorization harus ditegakkan di backend, bukan hanya di sidebar/frontend.
- Role `super-system` adalah protected role dan hanya boleh terlihat lengkap untuk akun super-system.
- Mutation penting wajib permission-gated dan diaudit.
- Secret tidak boleh dikirim plaintext ke frontend.
- Runtime operation seperti queue/scheduler tidak boleh menjadi bypass business authorization.
- Backup/restore harus signed, verified, dan punya dry-run sebelum destructive restore.

## Route dan frontend

Berbeda dari HR, route Console tidak memakai prefix `/console` untuk module utama.

Contoh:

```txt
Dashboard                : /dashboard
Access Control           : /access-control
User Management          : /users
System Settings          : /system-settings
Notification Templates   : /notification-templates
Activity Center          : /activity-center/read
Audit Logs               : /audit-logs
Login Activities         : /login-activities
Queue Monitor            : /queue-monitor
Scheduler Monitor        : /scheduler-monitor
Backup Restore           : /backup-restore
```

Frontend Console memakai namespace:

```txt
resources/js/pages/console/{module-slug}/
```

Contoh:

```txt
resources/js/pages/console/users/index.tsx
resources/js/pages/console/access-control/index.tsx
resources/js/pages/console/system-settings/index.tsx
resources/js/pages/console/backup-restore/index.tsx
```

## Contract wajib per module Console

Setiap module Console idealnya punya:

- `module.php`
- `routes.php`
- `permissions.php`
- `navigation.php` jika module punya menu sidebar
- `Providers/*ServiceProvider.php`
- `Policies/*`
- `Http/Controllers/*`
- `Http/Requests/*` untuk mutation
- `Services/*`
- `DTO/*` jika input/output mulai kompleks
- `Support/*` jika ada helper/permission list
- `Transactions/*` jika ada multi-write mutation
- feature test di `tests/Feature/*Test.php`

Pengecualian yang valid:

- `ActivityCenters` boleh tanpa navigation karena muncul sebagai dropdown header.
- Monitor runtime boleh tanpa model/migration jika hanya membaca runtime Laravel.
- Read-only module boleh tanpa FormRequest mutation.
- Backup/recovery module boleh punya service operational khusus seperti archive validator, signature service, dan SQL executor.

## Naming convention

Backend:

```txt
App\Modules\Console\AccessControls
App\Modules\Console\UserManagements
App\Modules\Console\BackupRestores
```

Frontend:

```txt
resources/js/pages/console/access-control
resources/js/pages/console/users
resources/js/pages/console/backup-restore
```

Permission:

```txt
access-control.view
users.view
users.create
system-settings.update
queue-monitor.manage
backup-restore.full-restore
```

Gunakan prefix permission yang stabil dan mudah dikelompokkan di Access Control.

## Role dan protected access

Role global utama:

- `super-system`: role tertinggi, protected, hidden untuk actor non-super-system.
- `admin`: role operasional Console umum.
- `staff`: role terbatas/read-only sesuai module.

Aturan `super-system`:

- tidak boleh dibuat dari UI biasa;
- tidak boleh rename/delete dari UI biasa;
- tidak boleh diassign dari User Management oleh actor non-super-system;
- hanya actor super-system yang boleh melihat role ini secara lengkap;
- tidak boleh menjadi target impersonation actor biasa.

## Authorization pattern

Gunakan pola berikut:

- route memakai middleware `auth`;
- controller/policy/FormRequest mengecek permission;
- frontend hanya UX guard, bukan security boundary;
- denial matrix harus punya feature test.

Contoh permission split:

| Area | View | Manage/mutation |
|---|---|---|
| Access Control | `access-control.view` / `roles.manage` | `roles.manage`, `access-control.create/update/delete` |
| Users | `users.view` | `users.create/update/delete/restore/force-delete/impersonate` |
| System Settings | `system-settings.view` | `system-settings.update` |
| Notification Templates | `notification-templates.view` | `notification-templates.update` |
| Audit Logs | `audit-logs.view` | none |
| Login Activities | `login-activities.view` | none |
| Queue Monitor | `queue-monitor.view` | `queue-monitor.manage` |
| Scheduler Monitor | `scheduler-monitor.view` | `scheduler-monitor.manage` |
| Backup Restore | `backup-restore.view` | `backup-restore.export/restore/full-export/full-restore` |

## Frontend rules

Console UI menjadi pola canonical untuk project lain.

Aturan utama:

- page composer `index.tsx` jangan membesar tanpa perlu;
- komponen besar masuk folder `*-components/`;
- type lokal masuk `types.ts`;
- destructive action memakai dialog/confirmation;
- Inertia mutation harus punya loading/disabled state;
- sidebar/navigation memakai Module Registry;
- search/help global boleh menjadi roadmap bila belum aktif;
- secret/config sensitive ditampilkan sebagai masked/configured state, bukan plaintext.

## Backend rules

Aturan backend Console:

- Controller hanya orkestrasi request/response.
- FormRequest memvalidasi input dan authorization mutation.
- Policy menjaga boundary akses.
- Service menjalankan use case.
- Transaction dipakai jika ada multi-write.
- Audit mencatat mutation penting tanpa raw secret.
- Runtime operation service tidak boleh menerima arbitrary command dari user.
- Backup restore service harus fail-closed untuk format/key/signature yang tidak valid.

## Data lifecycle

Console module tidak semuanya memakai soft delete.

| Area | Lifecycle |
|---|---|
| Users | soft delete, restore, force delete terbatas |
| Roles/permissions | protected mutation, tidak soft delete |
| System Settings | update-or-create, secret encrypted/masked |
| Notification Templates | update-or-create, active flag |
| Audit Logs | immutable read-only dari user |
| Login Activities | append-only security record |
| Activity Center | read model + read marker user |
| Queue/Scheduler Monitor | runtime read/action, tidak menyimpan domain state |
| Backup Restore | operational export/restore + audit |

## Backup dan recovery rule

Backup Restore punya aturan lebih ketat daripada module biasa:

- settings backup JSON bukan disaster recovery penuh;
- full backup aktif adalah signed ZIP v3;
- full backup mencakup database, storage public, dan private DMS;
- full restore hanya menerima signed `.zip`;
- dry-run default aktif dan harus dipakai untuk restore drill;
- restore database membuat user logout karena session/database bisa berubah;
- `BACKUP_SIGNATURE_KEY` dan `BACKUP_SIGNATURE_KEY_ID` wajib dikelola di environment/secret manager, bukan Git.

Runbook lengkap tersedia di [Backup signature runbook](../../reviews/2026-07-11-project-baseline/09-backup-signature-runbook.md).

## Quality gate per module Console

Jalankan gate terarah sesuai module:

```bash
php artisan test --filter=AccessControl
php artisan test --filter=UserManagement
php artisan test --filter=SystemSetting
php artisan test --filter=NotificationTemplate
php artisan test --filter=ActivityCenter
php artisan test --filter=AuditLog
php artisan test --filter=LoginActivity
php artisan test --filter=QueueMonitor
php artisan test --filter=SchedulerMonitor
php artisan test --filter=BackupRestore
php artisan module:validate
npm run typecheck
npm run build
git diff --check
```

Untuk checkpoint final:

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

## Checklist saat menambah atau mengubah module Console

- [ ] Pastikan module benar-benar concern Console, bukan domain bisnis.
- [ ] Tambahkan atau revisi `module.php`.
- [ ] Tambahkan route dengan middleware `auth`.
- [ ] Tambahkan permission di `permissions.php`.
- [ ] Tambahkan navigation jika perlu menu.
- [ ] Daftarkan policy/provider jika ada authorization.
- [ ] Gunakan FormRequest untuk mutation.
- [ ] Audit mutation penting tanpa raw secret.
- [ ] Tambahkan denial test untuk user tanpa permission.
- [ ] Tambahkan frontend disabled/loading state.
- [ ] Update docs/roadmap jika behavior/boundary berubah.

## Cross-link penting

- [Specification Console](specification.md)
- [Implementation Plan Console](implementation-plan.md)
- [Tasks Console](tasks.md)
- [Roadmap Console](roadmap.md)
- [ADR-001 Console operational foundation](decisions/001-console-operational-foundation.md)
- [Project module guide global](../../guides/project-module-guide.md)
- [Starterkit blueprint](../../architecture/starterkit-blueprint.md)
- [Baseline review](../../reviews/2026-07-11-project-baseline/README.md)
