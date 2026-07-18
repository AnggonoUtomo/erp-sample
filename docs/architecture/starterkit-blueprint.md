# Laravel 12 Modular Starterkit Blueprint

Dokumen ini adalah dokumentasi menyeluruh untuk starterkit. Isinya menjelaskan tujuan, teknologi, arsitektur, struktur folder, alur pengembangan dari awal sampai siap digunakan untuk project bisnis, serta aturan teknis yang perlu dijaga agar starterkit tetap sehat ketika berkembang menjadi banyak project.

## Tujuan Starterkit

Starterkit ini dibuat sebagai fondasi untuk membangun aplikasi bisnis modular berbasis Laravel 12 dan React. Targetnya bukan hanya membuat aplikasi dengan login dan dashboard, tetapi menyediakan pondasi yang bisa dipakai berulang untuk project besar seperti:

- Console atau back-office utama.
- Accounting.
- HR.
- CRM.
- Document Management.
- Attendance.
- Payroll.
- Project lain yang akan ditambahkan di masa depan.

Fokus utama starterkit:

- Module first.
- Multi-project namespace.
- Role dan permission siap pakai.
- UI console yang konsisten.
- Shared Kernel untuk konsep lintas project.
- Domain Event standard.
- Integration Layer untuk komunikasi antar project.
- Dokumentasi arsitektur yang bisa dipakai sebagai pegangan tim.

## Teknologi Utama

### Backend

- **PHP 8.2+**  
  Runtime utama aplikasi.

- **Laravel 12**  
  Framework backend, routing, service container, middleware, queue, mail, validation, policy, scheduler, dan testing.

- **Inertia Laravel 2**  
  Jembatan antara Laravel backend dan React frontend tanpa REST API terpisah untuk halaman internal.

- **Spatie Laravel Permission**  
  Role dan permission management. Dipakai untuk akses user, module, menu, dan policy.

- **Spatie Laravel Media Library**  
  Upload dan manajemen file/media, termasuk avatar user dan kebutuhan dokumen di module masa depan.

- **Tighten Ziggy**  
  Menggunakan route Laravel dari frontend React.

- **Laravel Queue**  
  Menangani email, export, backup, notification, dan proses berat.

- **Laravel Scheduler**  
  Menjalankan task berkala seperti scheduler heartbeat dan proses periodik.

- **Laravel Mail**  
  Mengirim email credential, reset password, test SMTP, dan notifikasi.

- **Laravel Policy/Gate**  
  Authorization per aksi.

- **Laravel Pint**  
  Formatter PHP.

- **PHPUnit**  
  Test suite backend.

### Frontend

- **React 19**  
  Library UI utama.

- **TypeScript**  
  Typing untuk frontend.

- **Inertia React 2**  
  Page rendering dan navigation dari Laravel ke React.

- **Vite 6**  
  Build tool frontend.

- **Tailwind CSS 4**  
  Styling utility-first.

- **Radix UI**  
  Primitive component untuk dialog, dropdown, avatar, checkbox, select, tooltip, navigation menu, dan lain-lain.

- **Lucide React**  
  Icon system.

- **Sonner**  
  Toast notification.

- **Headless UI**  
  Primitive UI tambahan.

- **ESLint, Prettier, TypeScript ESLint**  
  Linting dan formatting frontend.

### Tooling

- **Composer** untuk dependency PHP.
- **NPM** untuk dependency JavaScript.
- **Concurrently** untuk menjalankan server, queue, log, dan Vite secara bersamaan.
- **Laravel Pail** untuk melihat log aplikasi.

## Struktur Besar Aplikasi

```txt
app/
  Http/
  Models/
  Modules/
    Console/
      AccessControls/
      UserManagements/
      SystemSettings/
      ...
  Shared/
  Integration/
  Support/

resources/js/
  layouts/
  pages/
    console/
      auth/
      users/
      access-control/
      system-settings/
      ...

docs/
  README.md
  architecture/
    starterkit-blueprint.md
    shared-kernel.md
    integration-layer.md
  guides/
    project-module-guide.md
  projects/
    hr/
      roadmap.md
      module-guide.md
    accounting/
      roadmap.md
    crm/
      roadmap.md
    document-management/
      roadmap.md
    attendance/
      roadmap.md
    payroll/
      roadmap.md
  planning/
    PenyusunanProjectKedepan.txt
```

## Evolusi Pembuatan Starterkit

### 1. Laravel React Starter

Fondasi awal berasal dari Laravel React starter:

- Laravel sebagai backend.
- React sebagai frontend.
- Inertia sebagai penghubung.
- Auth dasar Laravel sebagai titik awal.

Setelah itu, auth disesuaikan agar login utama masuk melalui Console.

### 2. Console UI

UI Console dibentuk sebagai workspace utama:

- Floating sidebar.
- Floating sticky header.
- Dark/light toggle manual.
- Profile menu di header.
- Sidebar menu categorized.
- Shortcut keyboard di module tertentu.
- Sonner notification untuk aksi CRUD.
- Error boundary frontend.

Console menjadi project utama yang membawa fitur administrasi dasar.

### 3. User Management dan Access Control

Module user dan access control dibuat sebagai fitur administrasi utama.

Fitur:

- User management.
- Role management.
- Permission management.
- Policy dan middleware `can`.
- Spatie Permission.
- Avatar user dengan Media Library.
- Crop image.
- Activation/reset credential flow.
- Impersonate user.
- Soft delete user dengan restore dan force delete berbasis permission.
- Preview permission user yang memisahkan permission dari role dan direct permission tambahan.

Catatan Access Control: `roles` dan `permissions` dari Spatie Permission diperlakukan sebagai konfigurasi authorization aktif. Untuk menjaga cache permission, guard, dan pivot tetap sederhana, module Access Control tidak memakai soft delete. Jika nanti perlu approval/archive role, buat layer status/arsip khusus di atas role, bukan mengubah perilaku dasar Spatie tanpa alasan kuat.

### 4. System Settings

System Settings dibuat sebagai pusat konfigurasi aplikasi.

Fitur:

- SMTP settings.
- Test email SMTP.
- Queue email.
- App name, logo, favicon.
- Timezone dan date format.
- Default pagination.
- Security policy.
- Password policy.
- Maintenance mode.
- Google Maps API Key dan Map ID untuk module yang membutuhkan map/koordinat.
- System Health Panel.
- Environment Info read-only.
- Backup & Restore settings.
- Backup & Restore database/server.

### 5. Observability dan Operasional

Starterkit dilengkapi fitur operasional:

- Audit Log.
- Login Activity.
- Activity/Notification Center.
- Queue Monitor.
- Scheduler Monitor.
- Notification Template.
- System Health Panel.

Tujuannya agar aplikasi tidak hanya bisa dipakai, tapi juga bisa dipantau.

### 6. Module Contract Formal

Module dibuat punya contract yang jelas lewat file:

```txt
module.php
routes.php
permissions.php
navigation.php
Providers/*ServiceProvider.php
```

`module.php` menjadi metadata formal module:

```php
return [
    'name' => 'AccessControls',
    'project' => 'Console',
    'title' => 'Kontrol Akses',
    'slug' => 'access-controls',
    'description' => 'Role dan permission management untuk console.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [],
    'listeners' => [],
    'integrations' => [],
];
```

Discovery dilakukan oleh:

- `App\Support\Modules\ModuleRegistry`
- `App\Support\Modules\ModuleServiceProvider`
- `App\Support\Modules\ModulePermissionRegistry`

### 7. Module Generator Command

Command generator dibuat:

```bash
php artisan make:module ChartOfAccounts --project=Accounting
```

Output backend:

```txt
app/Modules/Accounting/ChartOfAccounts/
```

Output frontend:

```txt
resources/js/pages/accounting/chart-of-accounts/
```

Generator membuat struktur:

```txt
DTO/
Events/
Http/
  Controllers/
  Requests/
Integrations/
Listeners/
Policies/
Providers/
Services/
Support/
Transactions/
module.php
navigation.php
permissions.php
routes.php
```

### 8. Shared Kernel

Shared Kernel dibuat di:

```txt
app/Shared/
```

Isinya:

- Contract umum.
- Base DTO.
- Domain Event base.
- Value Object.
- Result object.
- Shared exception.

Shared Kernel dipakai untuk konsep lintas project, bukan untuk logic bisnis spesifik.

### 9. Domain Event Standard

Domain Event distandarkan supaya module bisa berkomunikasi secara loosely coupled.

Komponen:

- `DomainEvent`
- `DomainEventDispatcher`
- `DomainEventSubscriber`
- `BaseDomainEvent`
- `DispatchesDomainEvents`
- `LaravelDomainEventDispatcher`

Module bisa mendaftarkan event dan listener di `module.php`.

### 10. Integration Layer

Integration Layer dibuat di:

```txt
app/Integration/
```

Komponen:

- `IntegrationAdapter`
- `IntegrationContext`
- `IntegrationProjector`
- `IntegrationMessageData`
- `EventIntegrationContext`
- `IntegrationRegistry`

Tujuannya agar project besar seperti `Payroll`, `Attendance`, dan `Accounting` tidak saling membaca model internal secara langsung.

## Arsitektur Module

Setiap module idealnya mengikuti pola:

```txt
FormRequest
DTO
Service
Transaction
Policy
Model
Event
Listener / Job
Integration Adapter / Projector
```

Alur umum create/update:

```txt
Controller
  -> FormRequest
  -> DTO
  -> Service
  -> Transaction
  -> Model
  -> Domain Event
  -> Listener / Integration Layer
```

## Project Namespace

Starterkit memakai konsep project namespace:

```txt
app/Modules/Console
app/Modules/Accounting
app/Modules/CRM
app/Modules/DocumentManagement
app/Modules/Attendance
app/Modules/Payroll
```

Frontend mengikuti:

```txt
resources/js/pages/console
resources/js/pages/accounting
resources/js/pages/crm
resources/js/pages/document-management
resources/js/pages/attendance
resources/js/pages/payroll
```

## Project Console

`Console` adalah project inti starterkit.

Module yang sudah tersedia:

- `AccessControls`
- `ActivityCenters`
- `AuditLogs`
- `BackupRestores`
- `LoginActivities`
- `NotificationTemplates`
- `QueueMonitors`
- `SchedulerMonitors`
- `SystemSettings`
- `UserManagements`

Console menangani administrasi, konfigurasi, observability, dan fondasi operasional.

## Roadmap Project Bisnis

Dokumen roadmap yang sudah tersedia:

- `docs/projects/accounting/roadmap.md`
- `docs/projects/hr/roadmap.md`
- `docs/projects/crm/roadmap.md`
- `docs/projects/document-management/roadmap.md`
- `docs/projects/attendance/roadmap.md`
- `docs/projects/payroll/roadmap.md`

Roadmap ini belum berupa implementasi final, tapi menjadi arah desain awal untuk module, permission, event, integrasi, dan UI/UX.

## Role dan Permission

Starterkit memakai Spatie Permission.

Permission module berasal dari:

- `permissions.php`
- `Support\Permissions`
- `ModulePermissionRegistry`

User auth dibagikan ke frontend melalui Inertia:

- user
- roles
- permissions
- super system flag

Super admin dapat diberi bypass melalui `Gate::before`.

## Media dan File Upload

Upload file memakai Spatie Media Library.

Pemakaian saat ini:

- Avatar user.
- Upload logo/favicon.
- Attachment untuk module masa depan.

Pemakaian masa depan:

- Document Management.
- Bukti attendance.
- Attachment journal.
- Payroll document.

## Notification dan Email

Starterkit mendukung:

- SMTP configuration.
- Test SMTP email.
- Queue email.
- Notification template.
- Credential activation/reset link.

Email berat atau proses massal sebaiknya selalu masuk queue.

## Queue dan Scheduler

Queue dipakai untuk:

- Email.
- Export.
- Backup.
- Notification.
- Proses integrasi berat.

Scheduler dipakai untuk:

- Heartbeat scheduler.
- Proses berkala.
- Maintenance task.
- Reminder atau automation masa depan.

Queue Monitor dan Scheduler Monitor membantu melihat kondisi runtime.

## Backup dan Restore

Fitur backup mendukung:

- Backup settings.
- Restore settings.
- Full database/server backup.
- Restore database.

Restore full database dapat mengubah state user/session, sehingga perlu kehati-hatian.

## Data Lifecycle dan Soft Delete

Starterkit memakai soft delete untuk data yang menjadi master, identitas, histori bisnis, atau referensi lintas module.

Implementasi saat ini:

- `users` memakai soft delete melalui module `Console/UserManagements`.
- `hr_departements` memakai soft delete melalui module `HR/Departements`.
- `hr_positions`, `hr_job_levels`, dan `hr_work_locations` memakai soft delete untuk menjaga histori master HR.

`roles` dan `permissions` tidak memakai soft delete karena merupakan konfigurasi authorization Spatie. Delete pada Access Control tetap aksi terkontrol dan harus dijaga melalui permission/policy.

Operasional soft delete user:

- delete user berarti arsip, bukan hapus permanen;
- restore user tersedia untuk permission `users.restore`;
- force delete hanya untuk user yang sudah diarsipkan dan membutuhkan permission `users.force-delete`;
- role, permission, dan media avatar tetap dipertahankan saat user diarsipkan agar restore tidak kehilangan konteks akses.

Keputusan ini menjaga audit, role/permission pivot, media avatar, struktur organisasi, dan referensi lintas module tetap bisa ditelusuri. Detail aturan per project tersedia di:

```txt
docs/architecture/data-lifecycle.md
```

## Security

Lapisan security yang tersedia:

- Laravel auth.
- Spatie role/permission.
- Policy.
- Middleware `can`.
- Super admin gate.
- Password policy.
- Security policy.
- Maintenance mode.
- Audit log.
- Login activity.
- Impersonate guard.

Hal yang perlu dijaga:

- Jangan membuat route mutasi tanpa policy.
- Jangan expose data sensitif lewat Inertia props tanpa filter.
- Jangan memakai permission terlalu umum untuk aksi berisiko.
- Jangan biarkan public file URL melewati authorization untuk dokumen private.

## UI/UX Standard

Frontend mengikuti gaya Console:

- App layout konsisten.
- Sidebar categorized.
- Header sticky/floating.
- Dialog untuk aksi destruktif.
- Sonner untuk success notification.
- Modal/dialog untuk error penting.
- Table dengan pagination bar.
- Detail panel kanan untuk preview.
- Komponen dipisah jika page mulai besar.

Struktur frontend module:

```txt
resources/js/pages/{project}/{module}/
  index.tsx
  types.ts
  options.ts
  {module}-components/
```

`index.tsx` sebaiknya menjadi page composer, bukan tempat semua logic UI ditumpuk.

## Testing

Test yang tersedia mencakup:

- Auth.
- Access Control.
- User Management.
- System Setting.
- Audit Log.
- Login Activity.
- Backup Restore.
- Queue Monitor.
- Scheduler Monitor.
- Module generator.
- Module registry.
- Shared Kernel.
- Integration Layer.

Command:

```bash
php artisan test
```

Frontend build:

```bash
npm run build
```

PHP formatting:

```bash
vendor/bin/pint
```

Frontend lint:

```bash
npm run lint
```

## Alur Membuat Project Baru

Contoh membuat project Accounting:

```bash
php artisan make:module ChartOfAccounts --project=Accounting
php artisan make:module Journals --project=Accounting
php artisan make:module Reports --project=Accounting
```

Lalu:

1. Review `module.php`.
2. Review `permissions.php`.
3. Review `navigation.php`.
4. Buat FormRequest.
5. Buat DTO.
6. Buat Service.
7. Buat Transaction.
8. Buat Policy.
9. Buat Model dan migration jika perlu.
10. Buat Event jika aksi perlu dikomunikasikan.
11. Buat Integration Adapter/Projector jika data dipakai project lain.
12. Buat UI components.
13. Tambahkan tests.

## Alur Komunikasi Antar Project

Contoh Attendance ke Payroll:

```txt
AttendancePeriodClosed
  -> Domain Event
  -> Listener
  -> Integration Adapter
  -> IntegrationMessageData
  -> Payroll Snapshot Projector
  -> Payroll input ready
```

Contoh Payroll ke Accounting:

```txt
PayrollRunApproved
  -> Domain Event
  -> Integration Adapter
  -> Accounting draft journal
```

Aturan penting:

- Jangan import model internal project lain.
- Gunakan event payload, message, snapshot, atau contract.
- Gunakan `correlation_id` untuk trace.
- Gunakan queue jika proses berat.

## Checklist Module Siap

- `module.php` lengkap.
- `routes.php` ada.
- `permissions.php` ada.
- `navigation.php` ada jika punya menu.
- Provider terdaftar.
- Policy tersedia.
- FormRequest tersedia untuk mutasi.
- DTO digunakan untuk input service.
- Service berisi use case.
- Transaction membungkus write operation.
- Event dibuat untuk aksi penting.
- Listener/Job dipakai untuk proses async.
- Integration adapter/projector dibuat jika lintas project.
- Test route dan permission tersedia.
- Test operasi utama tersedia.
- UI dipisah menjadi komponen.

## Checklist Production

- `.env` sesuai environment.
- `APP_DEBUG=false`.
- Queue worker berjalan.
- Scheduler cron berjalan.
- Storage link siap jika memakai public media.
- SMTP sudah dites.
- Backup strategy jelas.
- Permission seeder dijalankan.
- Super admin tersedia.
- Maintenance mode diuji.
- Log dan monitoring aktif.
- Test suite hijau.
- Build frontend berhasil.

## Prinsip Arsitektur

Pegangan utama starterkit ini:

- Module boleh mandiri.
- Project boleh punya domain sendiri.
- Shared Kernel harus kecil.
- Domain Event harus membawa data publik yang stabil.
- Integration Layer melindungi boundary antar project.
- UI mengikuti pola Console.
- Test menjaga perubahan.
- Dokumentasi mengikuti arsitektur aktual.

## Batasan Saat Ini

Starterkit ini sudah memiliki fondasi lengkap, tetapi beberapa hal masih berupa platform/foundation:

- Project bisnis seperti HR/Accounting/CRM/Payroll belum diimplementasikan.
- Integration Layer belum memiliki adapter nyata karena project bisnis belum ada.
- Shared Kernel masih minimal dan akan bertambah sesuai kebutuhan nyata.
- Project generator khusus belum dibuat, baru module generator.

Ini kondisi yang sehat: starterkit siap dipakai, namun belum terlalu berat oleh abstraksi yang belum terbukti.

## Rekomendasi Langkah Berikutnya

Urutan paling sehat:

1. Pilih project bisnis pertama, misalnya `Accounting`.
2. Buat module foundation pertama dengan `make:module`.
3. Implementasikan satu use case nyata end-to-end.
4. Jika pola berulang, baru tingkatkan generator.
5. Tambahkan adapter Integration Layer saat ada komunikasi antar project nyata.
6. Update roadmap dan dokumentasi berdasarkan pengalaman implementasi.

Dengan begitu, starterkit tetap hidup sebagai fondasi produktif, bukan hanya kumpulan abstraksi.
