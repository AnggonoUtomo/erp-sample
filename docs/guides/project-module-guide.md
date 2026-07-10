# Panduan Membuat Project Modular

Dokumen ini menjelaskan cara membuat project baru di dalam starterkit. Dalam konteks starterkit ini, **project** adalah group namespace besar seperti `Console`, `HR`, `CRM`, atau project bisnis lain. Di dalam project tersebut baru ada module fitur.

Untuk panduan yang fokus pada project yang sedang dikerjakan saat ini, gunakan:

- `docs/projects/hr/module-guide.md`
- `docs/projects/hr/roadmap.md`

## Konsep Struktur

```txt
app/Modules/
  Console/
    UserManagements/
    AccessControls/
    SystemSettings/
  HR/
    Departements/
    Positions/
    Employees/

resources/js/pages/
  console/
    users/
    access-control/
    system-settings/
  hr/
    Departements/
    positions/
    employees/
```

Aturan namespace backend mengikuti folder:

```txt
app/Modules/hr/departements
```

menjadi:

```php
App\\Modules\\HR\\Departements
```

Aturan path frontend mengikuti kebab-case:

```txt
resources/js/pages/hr/departements
```

Untuk project yang namanya acronym dan ditulis full uppercase, generator menjaga slug tetap natural. Contoh `HR` menjadi `hr`, bukan `h-r`.

## Aturan Route Dan Frontend Generator

Generator membedakan project `Console` dan project bisnis/non-Console.

Untuk project default `Console`, route tidak memakai prefix project:

```bash
php artisan make:module Reports
```

Output utama:

```txt
Backend  : app/Modules/Console/Reports
Route    : /reports
Route name: reports.*
Frontend : resources/js/pages/console/reports/index.tsx
```

Untuk project non-Console, route dan frontend memakai prefix project:

```bash
php artisan make:module Departements --project=HR
```

Output utama:

```txt
Backend  : app/Modules/hr/departements
Route    : /hr/departements
Route name: hr.departements.*
Frontend : resources/js/pages/hr/departements/index.tsx
```

Aturan ini dibuat supaya setiap project bisnis punya boundary URL yang jelas, sementara Console tetap menjadi area administrasi utama tanpa prefix tambahan.

Project bisnis boleh memiliki login dan dashboard sendiri selama tetap memakai guard/session Laravel yang sama. Contoh project HR:

```txt
Login     : /hr/login
Dashboard : /hr/dashboard
Module    : /hr/departements
```

Pemisahan halaman login/dashboard membuat konteks project lebih jelas untuk user, sementara security policy, audit login, password reset, dan session tetap dikelola terpusat.

## Membuat Module Dalam Project

Gunakan command:

```bash
php artisan make:module Departements --project=HR
```

Output:

```txt
app/Modules/hr/departements/
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

resources/js/pages/hr/departements/index.tsx
```

Format shorthand juga tersedia:

```bash
php artisan make:module HR:Departements
php artisan make:module HR/Departements
```

Untuk membuat module di project default `Console`:

```bash
php artisan make:module Reports
```

Project default dapat diubah melalui `.env`:

```env
MODULE_DEFAULT_PROJECT=HR
```

## Kenapa Project Harus Eksplisit

Perintah seperti ini ambigu:

```bash
php artisan make:module HR
```

Command tidak bisa tahu apakah `HR` adalah nama project atau nama module. Karena itu, untuk project non-default gunakan:

```bash
php artisan make:module NamaModule --project=HR
```

atau:

```bash
php artisan make:module HR:NamaModule
```

## Contract Wajib Per Module

Setiap module sebaiknya punya file contract berikut:

- `module.php` untuk metadata formal module.
- `routes.php` untuk route web module.
- `permissions.php` untuk daftar permission dan default role permission.
- `navigation.php` untuk sidebar/menu.
- `Providers/*ServiceProvider.php` untuk policy, gate, binding, event, dan bootstrapping module.

File tersebut akan di-discover otomatis oleh:

- `App\Support\Modules\ModuleRegistry`
- `App\Support\Modules\ModuleServiceProvider`
- `App\Support\Modules\ModulePermissionRegistry`

Contoh `module.php`:

```php
use App\\Modules\\HR\\Departements\Providers\DepartementsServiceProvider;

return [
    'name' => 'departements',
    'project' => 'HR',
    'title' => 'departements',
    'slug' => 'departements',
    'description' => 'Master Departement dan struktur organisasi dasar HR.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        DepartementsServiceProvider::class,
    ],
    'dependencies' => [
        'Console.AccessControls',
    ],
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

Field penting:

- `name`: nama module dalam StudlyCase.
- `project`: nama project/group, misalnya `Console`, `HR`, `CRM`.
- `title`: nama tampil untuk dokumentasi/UI.
- `slug`: identifier URL-friendly untuk module.
- `enabled`: jika `false`, module tidak ikut route/provider/navigation/permission discovery.
- `providers`: daftar service provider resmi milik module.
- `dependencies`: daftar dependency module dalam format `Project.Module`.
- `exports`: contract yang disediakan module untuk runtime discovery.
- `events`: daftar domain event publik yang diterbitkan module.
- `listeners`: mapping event ke listener yang ingin didaftarkan module.
- `integrations`: daftar adapter/projector lintas project yang disediakan module.

## Pola Backend Yang Disarankan

Untuk module bisnis baru, gunakan alur:

```txt
FormRequest
DTO
Service
Transaction
Policy
Model
Event
Listener/Job
```

Contoh alur create:

```txt
Controller
  -> FormRequest validasi input
  -> DTO normalisasi data
  -> Service menjalankan use case
  -> Transaction menjaga atomic write
  -> Model persist data
  -> Event memberitahu module lain
```

## Pola Frontend Yang Disarankan

Untuk halaman Inertia:

```txt
resources/js/pages/hr/departements/
  index.tsx
  types.ts
  options.ts
  Departements-components/
    departement-header.tsx
    departement-table.tsx
    departement-form.tsx
```

Panduan:

- `index.tsx` hanya menjadi page composer.
- Simpan type lokal di `types.ts`.
- Simpan opsi statis di `options.ts`.
- Komponen besar dipisah ke folder `*-components`.
- Ikuti gaya UI/UX yang sudah ada di module `Console`.

## Komunikasi Antar Project

Hindari import langsung dari module project lain jika bukan contract publik.

Direkomendasikan:

- **Shared Kernel** untuk value object, interface, enum, dan helper lintas project.
- **Module Contract** untuk interface publik antar module.
- **Domain Event** untuk komunikasi longgar antar module.
- **Job/Queue** untuk proses berat atau integrasi asynchronous.

Shared Kernel tersedia di `app/Shared`. Detail penggunaannya ada di `docs/architecture/shared-kernel.md`.
Integration Layer tersedia di `app/Integration`. Detail penggunaannya ada di `docs/architecture/integration-layer.md`.

Contoh yang baik:

```txt
HR\\Departements
  -> dispatch DepartementCreated event

Console\ActivityCenters
  -> listen event
  -> tampilkan notifikasi aktivitas
```

Contoh yang perlu dihindari:

```php
use App\\Modules\\HR\\Departements\Models\Departement;
```

dari project lain hanya untuk membaca detail internal module.

## Checklist Saat Membuat Project Baru

- Tentukan nama project dalam StudlyCase, misalnya `HR`.
- Tentukan slug frontend, misalnya `hr`.
- Buat module pertama dengan `php artisan make:module NamaModule --project=HR`.
- Review `navigation.php` supaya menu masuk kategori yang benar.
- Review `permissions.php` supaya role default tidak terlalu longgar.
- Tambahkan policy sebelum fitur menyimpan/mengubah data.
- Tambahkan test feature untuk route, permission, dan operasi utama.
- Pecah UI menjadi komponen sebelum `index.tsx` terlalu panjang.

## Checklist Dokumentasi Perubahan

Setiap perubahan yang mengubah cara kerja project harus langsung diikuti update dokumen terkait.

- Jika mengubah generator module/project, update `docs/guides/project-module-guide.md` dan `app/Modules/README.md`.
- Jika mengubah struktur module contract, update `docs/architecture/starterkit-blueprint.md`, `docs/guides/project-module-guide.md`, dan `app/Modules/README.md`.
- Jika mengubah Shared Kernel, update `docs/architecture/shared-kernel.md`.
- Jika mengubah Integration Layer, update `docs/architecture/integration-layer.md`.
- Jika mengubah seeder demo atau role project tertentu, update panduan project terkait, misalnya `docs/projects/hr/module-guide.md`.
- Jika menambah fitur besar dalam project tertentu, update roadmap project terkait, misalnya `docs/projects/hr/roadmap.md`.
- Jika mengubah UI/UX pattern global, update `docs/architecture/starterkit-blueprint.md`.

## Checklist Sebelum Production

- Semua route memakai middleware `auth`.
- Semua aksi mutasi dilindungi policy/middleware `can`.
- Permission module masuk ke seeder modular.
- Audit log aktif untuk aksi penting.
- Event penting terdokumentasi.
- Queue digunakan untuk email, export, import, dan proses berat.
- Tidak ada import liar antar project tanpa contract.
