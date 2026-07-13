# 08 — Mutation Authorization Matrix

Status: verified 2026-07-12. Scope: mutation route domain aplikasi yang memerlukan permission khusus.

## Matrix

| Route/pattern | Mutation | Authorization owner | Runtime denial evidence |
| --- | --- | --- | --- |
| `access-control.roles.*`, `access-control.permissions.*` | create/update/sync/delete | role policy dan `access-control.manage` | `AccessControlTest` |
| `activity-center.read` | mark read | `activity-center.read` | `ActivityCenterTest` |
| `backup-restore.restore`, `backup-restore.full.restore` | destructive restore | backup restore policy/permission | `BackupRestoreTest` |
| `document-management.*` | ingest/version/archive/restore/delivery issue/consume | DMS request, permission, access authority | `DocumentIngestionTest`, `DocumentVersioningTest`, `DocumentLifecycleTest`, `DocumentDeliveryTest` |
| `notification-templates.update` | update template | `notification-templates.update` | `NotificationTemplateTest` |
| `queue-monitor.failed.*` | retry/forget/flush | `queue-monitor.manage` | `QueueMonitorTest` |
| `scheduler-monitor.run` | run due tasks | `scheduler-monitor.manage` | `SchedulerMonitorTest` |
| `system-settings.*` | update/test configuration | `system-settings.update` | `SystemSettingTest` |
| `users.store/update/destroy/restore/force-destroy` | user lifecycle | user policy/permission per action | `UserManagementTest` |
| `users.impersonate` | begin impersonation | `users.impersonate` dan target rules | `UserImpersonationTest` |
| `hr.departements.*` | create/update/delete | departement policy | `HRDepartementTest` |
| `hr.employees.*` | create/update/delete/restore/force-delete | employee request/policy | `HREmployeeTest` |
| `hr.employee-documents.*` | create metadata; mutation berikutnya wajib masuk inventaris | employee document request/policy | `HREmployeeDocumentAuthorizationTest` |
| `hr.employment-statuses.*` | create/update/delete/restore/force-delete | employment status request/policy | `HREmploymentStatusTest` |
| `hr.employment-types.*` | create/update/delete/restore/force-delete | employment type request/policy | `HREmploymentTypeTest` |
| `hr.job-levels.*` | create/update/delete/restore/force-delete | job level policy | `HRJobLevelTest` |
| `hr.organization-structures.*` | create/update/delete/restore/force-delete | organization structure request/policy | `HROrganizationStructureTest` |
| `hr.positions.*` | create/update/delete | position policy | `HRPositionTest` |
| `hr.hr-reference-data.*` | data/category lifecycle | reference data/category request/policy | `HRReferenceDataTest` |
| `hr.work-locations.*` | create/update/delete/restore/force-delete | work location policy | `HRWorkLocationTest` |

`MutationRouteAuthorizationTest` menginventaris route registry saat test berjalan dan gagal bila mutation baru pada kelompok di atas tidak memiliki middleware `auth`.

## Aturan evidence

- User authenticated tanpa permission harus menerima `403`.
- Resource route diuji menggunakan record valid; archived record digunakan untuk restore/force-delete. `404` tidak diterima sebagai evidence authorization.
- Test memastikan record penting tetap ada/archived atau tabel setting tetap kosong setelah denial.
- Guest boundary dibuktikan oleh executable middleware contract; endpoint high-risk backup juga mempunyai request-level guest test.

## Pengecualian terkontrol

- Login, logout, password reset/confirmation, dan verification adalah authentication lifecycle, bukan permission domain.
- `settings/profile` dan `settings/password` adalah self-service route yang dilindungi `auth` serta current-password validation.
- `users.impersonate.stop` mengakhiri session privilege milik actor aktif dan sengaja tidak memerlukan permission baru.
- Local `storage/{path}` adalah route framework untuk disk local/temporary URL dan bukan module mutation contract.

## Cara verifikasi

```text
php artisan test --compact tests/Feature/MutationRouteAuthorizationTest.php
php artisan test --compact
composer quality:check
```
