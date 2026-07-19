# Specification: Console Global Search / Command Palette

## Objective

Membangun global search/command palette untuk Console agar user bisa menemukan menu dan halaman dengan cepat tanpa harus membuka sidebar manual.

Untuk MVP, search bersifat:

- **read-only**;
- **permission-aware**;
- **navigation-first**;
- **tidak membaca data sensitif**;
- **tidak melakukan mutation**.

User utama:

- `super-system` yang butuh akses cepat ke semua area Console;
- admin/staff Console yang hanya boleh melihat result sesuai permission;
- HR Manager atau role bisnis lain yang memakai sidebar dan menu project.

Success terlihat saat user menekan `Ctrl+K` atau `Cmd+K`, mengetik kata seperti `user`, `backup`, `laporan`, `karyawan`, atau `settings`, lalu hanya melihat menu yang boleh ia akses.

## Assumptions

- Header saat ini sudah punya search placeholder di `resources/js/components/app-menu-header.tsx`.
- Sidebar/navigation saat ini berasal dari shared Inertia props `navigation`.
- Permission filtering di frontend adalah UX convenience; security tetap ditegakkan di backend route/policy.
- MVP tidak membutuhkan database table baru.
- MVP tidak membutuhkan dependency baru.
- Search result pertama boleh berasal dari navigation/menu saja.

## Requirement

### Functional requirement

1. User login bisa membuka command palette dari:
    - klik search box header;
    - keyboard `Ctrl+K`;
    - keyboard `Cmd+K` di macOS.
2. User guest tidak melihat/menjalankan command palette.
3. Search membaca daftar menu dari shared `navigation` yang sudah tersedia.
4. Result menampilkan:
    - title dalam Bahasa Indonesia jika translation tersedia;
    - group/kategori;
    - URL tujuan;
    - optional badge/description;
    - icon bila aman dan tersedia.
5. Result hanya menampilkan item yang user boleh akses berdasarkan permission item.
6. Result nested child menu ikut searchable.
7. Klik result menutup palette dan navigasi ke URL result via Inertia.
8. Empty state menjelaskan bahwa tidak ada hasil atau user tidak punya akses.
9. Keyboard navigation minimal:
    - `ArrowDown` / `ArrowUp`;
    - `Enter`;
    - `Escape`.
10. Query pendek boleh didukung, tetapi kosong menampilkan menu prioritas/terakhir atau instruksi singkat.

### Security requirement

- Search tidak boleh menampilkan result untuk menu tanpa permission.
- Search tidak boleh menampilkan secret/config value.
- Search tidak boleh menampilkan audit payload raw.
- Search tidak boleh menampilkan document number, backup file content, token, signed URL, atau path storage.
- Entity search masa depan wajib punya provider contract dan permission check sendiri.
- Semua endpoint search masa depan harus rate-limited dan auth-protected.

### UX requirement

- Bahasa UI menggunakan Bahasa Indonesia sederhana.
- Search box header tetap compact dan menyesuaikan breadcrumb.
- Command palette tidak membuat layout shift.
- Result mudah dibaca pada light/dark mode dan theme option yang ada.
- Loading/empty state jelas.

## Non-scope

- Full-text search dokumen.
- Indexing database semua module.
- Mutation command seperti delete/archive/restore/flush/backup.
- Search fuzzy kompleks dengan scoring external.
- External search service.
- Search publik untuk guest.
- Menyimpan histori query user.

## Project structure

Dokumentasi:

```txt
docs/projects/console/global-search/
  README.md
  specification.md
  implementation-plan.md
  tasks.md
  decisions/
    001-permission-aware-navigation-search-first.md
    002-entity-search-provider-contract.md
```

Rencana source code:

```txt
resources/js/components/
  app-menu-header.tsx
  global-command-palette.tsx

resources/js/lib/
  navigation-search.ts

tests/Feature/
  ConsoleGlobalSearchTest.php       # jika backend endpoint/provider ditambahkan
```

Jika nanti entity provider backend dibutuhkan:

```txt
app/Modules/Console/GlobalSearches/
  module.php
  routes.php
  permissions.php
  Http/Controllers/GlobalSearchController.php
  Http/Requests/GlobalSearchRequest.php
  Services/GlobalSearchService.php
  Contracts/EntitySearchProvider.php
  DTO/SearchResult.php
```

Catatan: MVP Task 01–02 boleh belum membuat module backend jika semua data cukup dari `navigation`.

## Interface design

### Frontend result type

```ts
type CommandPaletteResult = {
    id: string;
    title: string;
    group: string;
    url: string;
    keywords: string[];
    description?: string;
    badge?: string;
};
```

Aturan:

- `id` stabil dari group + URL.
- `title` adalah label user-facing.
- `group` adalah kategori menu user-facing.
- `url` wajib internal URL.
- `keywords` berisi title asli, title terjemahan, group, dan optional alias.

### Future backend provider contract

Jika search entity dibuka, setiap provider wajib menghasilkan result aman:

```php
interface EntitySearchProvider
{
    public function key(): string;

    public function label(): string;

    public function canSearch(SearchContext $context): bool;

    /**
     * @return list<SearchResult>
     */
    public function search(SearchQuery $query, SearchContext $context): array;
}
```

```php
final readonly class SearchResult
{
    public function __construct(
        public string $id,
        public string $provider,
        public string $type,
        public string $title,
        public string $group,
        public string $url,
        public ?string $description = null,
        public array $badges = [],
        public array $meta = [],
    ) {}
}
```

```php
final readonly class SearchQuery
{
    public function __construct(
        public string $term,
        public int $limit = 10,
    ) {}
}

final readonly class SearchContext
{
    public function __construct(
        public int $userId,
        public array $permissions,
        public string $guard = 'web',
    ) {}
}
```

Forbidden fields:

- password
- password_hash
- remember_token
- token
- secret
- api_key
- private_key
- signed_url
- backup path/content
- storage path
- document number raw
- document content
- private email payload body
- audit old/new raw values

Aturan contract:

- provider wajib read-only;
- provider wajib melakukan permission check sendiri;
- provider wajib memakai allowlist field output;
- aggregator wajib punya forbidden-field guard sebelum result dikirim ke frontend;
- URL result wajib internal;
- result limit default maksimal 10 per provider dan 20 total response;
- response provider tanpa permission berupa result kosong, bukan error detail;
- route masa depan wajib `auth`, rate-limited, dan validasi query.

Detail keputusan tersedia di [ADR-002: Entity search provider contract](decisions/002-entity-search-provider-contract.md).

### Users provider spike

Task 06 membuka provider entity pertama: `Users`.

Aturan output:

- provider key: `users`;
- type: `user`;
- permission endpoint: `global-search.search`;
- permission provider: `users.view`;
- URL result: internal route ke User Management dengan filter nama;
- field yang boleh tampil: `id`, `provider`, `type`, `title`, `group`, `url`, `description`, `badges`, `meta.userId`;
- email boleh dipakai untuk matching query, tetapi tidak dikirim di response MVP;
- `super-system` user disembunyikan dari actor non-`super-system`;
- frontend command palette belum mengonsumsi endpoint ini sampai integration UI entity search di-approve.

## Command design

Quality gate:

```bash
npm run typecheck
npm run test:frontend
npm run build
php artisan module:validate
php artisan test --filter=ConsoleGlobalSearch
git diff --check
```

Jika belum ada backend test untuk MVP navigation-only, `php artisan test --filter=ConsoleGlobalSearch` boleh diganti dengan targeted test yang relevan atau dilewati dengan catatan di task evidence.

## Boundaries

### Always

- Search result harus permission-aware.
- Search harus read-only.
- Search UI harus keyboard accessible.
- Gunakan shared `navigation` sebelum membuat endpoint baru.
- Reuse komponen UI/shadcn yang sudah ada.
- Dokumentasikan perubahan behavior pada docs project ini.

### Ask first

- Menambah dependency search/fuzzy baru.
- Membuat table/index search baru.
- Membuka entity search untuk data sensitif seperti user, employee, document, audit log, backup.
- Menambahkan command mutation dari command palette.
- Mengirim query search ke external service.

### Never

- Menampilkan secret/token/password/API key.
- Menampilkan raw audit payload.
- Menampilkan private DMS path atau signed token.
- Menggunakan frontend permission sebagai satu-satunya security untuk backend entity search.
- Membuat destructive action dari command palette pada MVP.

## Acceptance criteria

- [ ] Command palette terbuka dari click dan keyboard shortcut.
- [ ] Result menu/sidebar searchable dan nested child ikut masuk.
- [ ] Result tanpa permission tidak muncul.
- [ ] Klik result navigasi ke halaman tujuan.
- [ ] Empty state, keyboard navigation, dan escape behavior berfungsi.
- [ ] `npm run typecheck` dan `npm run build` hijau.
- [ ] Tidak ada data sensitif baru di props/result.

## Test plan

### Frontend

- Unit test `navigation-search.ts`:
    - flatten nested navigation;
    - translate alias/title;
    - filter permission;
    - rank exact/prefix/contains secara deterministik.
- Component/manual:
    - `Ctrl+K` membuka palette;
    - `Escape` menutup;
    - `Enter` memilih result;
    - empty state muncul saat query tidak cocok.

### Backend

MVP navigation-only:

- Tidak wajib backend route baru.
- `php artisan module:validate` tetap wajib.

Jika entity provider backend dibuat:

- route wajib `auth`;
- result wajib permission-aware;
- forbidden-field guard diuji;
- rate limit diuji;
- read-only/no mutation diuji.

## Open questions

- Apakah search entity `Users` dan `Employees` dibuka pada fase berikutnya, atau cukup navigation/menu sampai Console UX matang?
- Apakah command palette boleh menampilkan “recent pages” berbasis browser local storage, atau harus tanpa histori dulu?
- Apakah Help button akan digabung ke command palette sebagai result “Buka Dokumentasi”?
