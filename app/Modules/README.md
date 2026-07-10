# Module Contract

Struktur module memakai group/konteks di bawah `app/Modules`.

```txt
app/Modules/
  Console/
    AccessControls/
    SystemSettings/
    UserManagements/
  HR/
    Departements/
    Positions/
    Employees/
```

Setiap module dapat menyediakan file berikut di root folder module:

- `module.php` untuk metadata formal module.
- `routes.php` untuk route milik module.
- `permissions.php` untuk daftar permission dan default permission per role.
- `navigation.php` untuk item sidebar/menu module.
- `Providers/*ServiceProvider.php` untuk policy, gate, binding, event, dan bootstrapping module.

File akan di-discover otomatis oleh `App\Support\Modules\ModuleServiceProvider` dan `App\Support\Modules\ModuleRegistry`, termasuk module yang berada di dalam group seperti `Console` atau `HR`.

Panduan lebih detail tersedia di:

- `docs/architecture/starterkit-blueprint.md`
- `docs/README.md`
- `docs/guides/project-module-guide.md`
- `docs/projects/hr/module-guide.md`
- `docs/architecture/shared-kernel.md`
- `docs/architecture/integration-layer.md`
- `docs/projects/hr/roadmap.md`
- `docs/projects/accounting/roadmap.md`
- `docs/projects/crm/roadmap.md`
- `docs/projects/document-management/roadmap.md`
- `docs/projects/attendance/roadmap.md`
- `docs/projects/payroll/roadmap.md`

## Generator

Gunakan Artisan command untuk membuat module baru:

```bash
php artisan make:module Reports
```

Perintah di atas membuat module di project default dari `config/modules.php`, yaitu `Console`.

Untuk membuat module di project/group lain:

```bash
php artisan make:module Departements --project=HR
```

Output backend akan dibuat di `app/Modules/HR/Departements`, sedangkan halaman Inertia awal dibuat di `resources/js/pages/hr/Departements`.

Aturan route generator:

- Project `Console` memakai route tanpa prefix project, misalnya `php artisan make:module Reports` menghasilkan `/reports` dan route name `reports.*`.
- Project non-Console memakai prefix project, misalnya `php artisan make:module Departements --project=HR` menghasilkan `/hr/Departements` dan route name `hr.Departements.*`.
- Project acronym uppercase seperti `HR` akan dibuat sebagai slug `hr`, bukan `h-r`.

Catatan penting: generator tidak menebak project dari posisi terminal. Gunakan `--project` untuk konteks project yang eksplisit, atau ubah `MODULE_DEFAULT_PROJECT` di `.env` jika project aktif harian bukan `Console`.

## Aturan Update Dokumentasi

Setiap perubahan pada generator, module contract, struktur folder, route convention, permission convention, atau arsitektur lintas module wajib langsung diikuti update dokumen terkait. Untuk perubahan generator, minimal update:

- `docs/guides/project-module-guide.md`
- `app/Modules/README.md`

Contoh `module.php`:

```php
use App\Modules\HR\Departements\Providers\DepartementsServiceProvider;

return [
    'name' => 'Departements',
    'project' => 'HR',
    'title' => 'Departements',
    'slug' => 'Departements',
    'description' => 'Master departement dan struktur organisasi dasar HR.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        DepartementsServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [],
    'listeners' => [],
];
```

Contoh `permissions.php`:

```php
return [
    'permissions' => [
        'module.view',
        'module.create',
    ],
    'roles' => [
        'admin' => ['module.view', 'module.create'],
        'staff' => ['module.view'],
    ],
];
```

Contoh `navigation.php`:

```php
return [
    'group' => 'Produktivitas',
    'sort' => 40,
    'items' => [
        [
            'title' => 'Nama Menu',
            'url' => '/module-url',
            'icon' => 'Users',
            'permissions' => ['module.view'],
        ],
    ],
];
```
