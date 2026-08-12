# Context Pack — ARC-DDD-LITE-001

## Task Aktif

Restrukturisasi struktur modul dari flat structure ke DDD-Lite layered structure.

## Work Item Induk

- ID: ARC-DDD-LITE-001
- Kind: architecture-change
- Classification: CRITICAL
- Status: proposed

## Fakta Repository yang Terverifikasi

1. **Struktur saat ini**: Flat structure di `app/Modules/{Boundary}/{Module}/` dengan folder DTO, Events, Http, Integrations, Listeners, Models, Policies, Providers, Services, Support, Transactions, Database di root modul.

2. **Jumlah modul**: 18 modul (16 HR + DocumentManagement + Console)

3. **Module generator**: `MakeModuleCommand` di `app/Support/Modules/Commands/` generate struktur flat.

4. **Tests lokasi**: `tests/Feature/` dan `tests/Unit/` di root.

5. **Routes lokasi**: `routes/web.php`, `routes/api.php`, `routes/console.php` di root.

6. **Kontrak lintas modul**: HR IntegrationContracts menyediakan snapshot providers untuk DocumentManagement.

7. **Shared Kernel**: `app/Shared/` berisi ValueObjects (Money, DateRange), Events (BaseDomainEvent), DTO (DataObject), Contracts (DomainEvent, DomainEventDispatcher).

## Requirement dan Kriteria Penerimaan

### Requirement

1. Struktur modul mengikuti DDD-Lite layered structure sesuai acuan.
2. Module generator diupdate untuk generate struktur baru.
3. Tests dipindah ke dalam tiap modul.
4. Routes dipindah ke dalam tiap modul.
5. Namespace diupdate di semua file.
6. Semua tests harus pass setelah setiap phase.

### Kriteria Penerimaan

- [ ] Module generator generate struktur DDD-Lite
- [ ] Semua 18 modul dikonversi ke struktur baru
- [ ] Tests dipindah ke dalam modul
- [ ] Routes dipindah ke dalam modul
- [ ] Full test suite pass
- [ ] Aplikasi bisa diakses di browser
- [ ] Dokumentasi terupdate

## ADR / Kontrak / Boundary yang Relevan

- `docs/DDD-Lite-Modular-Monolith-Laravel-Acuan.md` — Acuan arsitektur
- `docs/03-architecture/MODULE-CATALOG.md` — Katalog modul
- `docs/03-architecture/DEPENDENCY-RULES.md` — Aturan dependency
- `docs/04-design/EVENT-CATALOG.md` — Katalog event

## File dan Area yang Diizinkan

- `app/Modules/` — Semua modul
- `app/Support/Modules/` — Module support
- `app/Shared/` — Shared Kernel
- `routes/` — Routes (hanya untuk migrasi)
- `tests/` — Tests (hanya untuk migrasi)
- `docs/` — Dokumentasi
- `composer.json` — Autoloading config

## File dan Area yang Dilarang

- `vendor/` — Tidak boleh diubah
- `node_modules/` — Tidak boleh diubah
- `bootstrap/` — Tidak boleh diubah
- `config/` — Tidak boleh diubah (kecuali ada kebutuhan spesifik)

## Pola Eksisting yang Harus Dipertahankan

1. **Module manifest**: `module.php` tetap di root modul dengan format yang sama.
2. **ServiceProvider**: Naming convention `{Module}ServiceProvider` dipertahankan.
3. **Route naming**: `Route::middleware(['auth'])->prefix('...')->name('...')` dipertahankan.
4. **Inertia pages**: Frontend path tidak berubah.
5. **Permission naming**: `{slug}.view`, `{slug}.create`, dll dipertahankan.
6. **Event naming**: Past tense (WorkLocationCreated, InvoicePaid) dipertahankan.

## Perintah Verifikasi

```bash
# Autoloading
composer dump-autoload

# Route check
php artisan route:list

# Module check
php artisan module:list

# Test per modul
php artisan test --filter=HRWorkLocation

# Full test suite
php artisan test

# Static analysis (jika ada)
php artisan pint
phpstan analyse
```

## Asumsi / Pertanyaan Terbuka / Risiko

### Asumsi

1. Frontend Inertia pages tidak perlu diubah path-nya.
2. Database schema tidak berubah.
3. Tidak ada kode di luar modul yang mengakses namespace modul secara langsung.

### Pertanyaan Terbuka

1. Apakah `app/Http/` di root masih diperlukan?
2. Apakah `app/Integration/` di root perlu direstrukturisasi?
3. Bagaimana dengan `app/Models/` di root (jika ada)?

### Risiko

1. **HIGH**: Namespace changes memutus autoloading — mitigasi dengan composer dump-autoload.
2. **HIGH**: Cross-module references hardcode namespace lama — mitigasi dengan grep search.
3. **MEDIUM**: Tests gagal setelah move — mitigasi dengan update namespace sistematis.
4. **MEDIUM**: Routes tidak ter-register — mitigasi dengan verify route:list.

</contents>