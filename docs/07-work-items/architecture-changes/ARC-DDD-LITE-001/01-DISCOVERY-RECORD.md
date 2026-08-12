# 01 Catatan Penemuan

> Fokus wajib: observasi arsitektur yang muncul saat development, bukti, dampak terhadap task aktif, dan pertanyaan terbuka.

## Metadata

```yaml
work_item: ARC-DDD-LITE-001
status: draft
owner: unassigned
last_updated: 2026-08-12
```

## Tujuan

Mendokumentasikan kondisi arsitektur saat ini sebelum restrukturisasi, termasuk struktur folder, namespace, dependency, dan pola yang ditemukan.

## Input dan Referensi

- docs/DDD-Lite-Modular-Monolith-Laravel-Acuan.md - Acuan arsitektur target
- app/Modules/ - Struktur modul saat ini
- app/Support/Modules/Commands/MakeModuleCommand.php - Module generator
- app/Support/Modules/ModuleRegistry.php - Registry modul
- app/Support/Modules/ModuleContractValidator.php - Validator kontrak modul

## Detail

### Struktur Saat Ini (Flat)

```
app/Modules/
├── HR/ (16 modul)
│   ├── WorkLocations/
│   ├── Positions/
│   ├── OrganizationStructures/
│   ├── Onboardings/
│   ├── Offboardings/
│   ├── Employees/
│   ├── EmployeeContracts/
│   ├── EmployeeDocuments/
│   ├── EmployeeMovements/
│   ├── EmploymentStatuses/
│   ├── EmploymentTypes/
│   ├── JobLevels/
│   ├── Departements/
│   ├── HRReferenceData/
│   ├── HRReports/
│   └── IntegrationContracts/
├── DocumentManagement/ (1 modul)
└── Console/ (11 modul)
    ├── UserManagements/
    ├── SystemSettings/
    ├── AuditLogs/
    ├── BackupRestores/
    ├── AccessControls/
    ├── ActivityCenters/
    ├── GlobalSearches/
    ├── LoginActivities/
    ├── NotificationTemplates/
    ├── QueueMonitors/
    └── SchedulerMonitors/
```

### Total Modul: 28

| Boundary | Jumlah Modul | Keterangan |
|---|---|---|
| HR | 16 | Core HR management |
| Console | 11 | System administration & monitoring |
| DocumentManagement | 1 | Document management |
| **Total** | **28** | |

### Modul Console yang Ditemukan

| No | Module | Tujuan | File Count (est) |
|---|---|---|---|
| 1 | UserManagements | User management, impersonation | ~20 |
| 2 | SystemSettings | System configuration, email, branding | ~30 |
| 3 | AuditLogs | Audit trail logging | ~10 |
| 4 | BackupRestores | Database backup & restore | ~25 |
| 5 | AccessControls | Access control management | ~15 |
| 6 | ActivityCenters | Activity tracking | ~15 |
| 7 | GlobalSearches | Cross-module search | ~15 |
| 8 | LoginActivities | Login activity monitoring | ~10 |
| 9 | NotificationTemplates | Notification template management | ~15 |
| 10 | QueueMonitors | Queue monitoring | ~10 |
| 11 | SchedulerMonitors | Scheduled task monitoring | ~10 |

### Struktur Target (DDD-Lite Layers)

```
app/Modules/{Boundary}/{Module}/
├── Application/
│   ├── Actions/
│   ├── DTO/
│   ├── Queries/
│   ├── Services/
│   └── Contracts/
├── Domain/
│   ├── Contracts/
│   ├── Entities/
│   ├── Events/
│   ├── Exceptions/
│   ├── Services/
│   └── ValueObjects/
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Models/
│   │   └── Repositories/
│   ├── Observers/
│   ├── Providers/
│   └── External/
├── Presentation/
│   ├── Controllers/
│   ├── Policies/
│   ├── Requests/
│   └── Resources/
├── Integration/
│   ├── Contracts/
│   ├── Listeners/
│   └── Services/
├── Database/
│   ├── Factories/
│   ├── Migrations/
│   └── Seeders/
├── Routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── Tests/
│   ├── Feature/
│   ├── Integration/
│   └── Unit/
├── module.php
├── permissions.php
├── navigation.php
├── ServiceProvider.php
└── README.md
```

### Kontrak Lintas Modul yang Ditemukan

1. **HR Integration Contracts** - Snapshot provider untuk employee data:
   - EmployeeSnapshotProvider - Digunakan oleh DocumentManagement
   - EmployeeContractSnapshotProvider - Digunakan oleh DocumentManagement
   - EmployeeDocumentComplianceSnapshotProvider - Digunakan oleh DocumentManagement
   - EmployeeAssignmentSnapshotProvider - Digunakan oleh DocumentManagement

2. **Console Modules Dependencies:**
   - GlobalSearches - Contracts dengan semua modul yang bisa di-search
   - AuditLogs - Listener untuk events dari semua modul
   - NotificationTemplates - Used by all modules for notifications
   - ActivityCenters - Tracks activities across modules

### Module Generator Saat Ini

MakeModuleCommand generate struktur flat:
- DTO/, Events/, Http/Controllers/, Http/Requests/
- Integrations/, Listeners/, Policies/, Providers/
- Services/, Support/, Transactions/

Generator perlu diupdate untuk generate struktur DDD-Lite.

### Tests Saat Ini

- Lokasi: tests/Feature/ dan tests/Unit/ (di root)
- Pattern: HRWorkLocationTest.php, HRPositionTest.php, ConsoleUserManagementTest.php, dll
- Target: Dipindah ke app/Modules/{Boundary}/{Module}/Tests/

### Routes Saat Ini

- Lokasi: routes/web.php, routes/api.php, routes/console.php
- Target: Dipindah ke app/Modules/{Boundary}/{Module}/Routes/

## Keputusan / Hasil

1. Struktur target mengikuti acuan DDD-Lite dengan 8 layer
2. Tests dan Routes dipindah ke dalam modul untuk memudahkan penelusuran
3. Module generator harus diupdate sebelum modul lain direstrukturisasi
4. Urutan pengerjaan: Generator -> Shared Kernel -> Modul dengan dependency banyak -> Modul dependen
5. **28 modul total** (16 HR + 11 Console + 1 DocumentManagement)

## Risiko dan Pertanyaan Terbuka

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Namespace changes memutus autoloading | High | Update composer.json autoload, run composer dump-autoload |
| Cross-module references hardcode namespace lama | High | Grep search semua namespace lama sebelum move |
| Tests gagal setelah move | Medium | Update namespace di tests, verify satu per satu |
| Routes tidak ter-register | Medium | Pastikan ServiceProvider load routes dari lokasi baru |
| Frontend Inertia pages tidak ketemu | Medium | Update path reference di controller |
| 28 modul = effort lebih besar | High | Prioritaskan modul critical dulu |

### Pertanyaan Terbuka

1. Apakah Shared/ di app/Shared/ perlu direstrukturisasi juga?
2. Apakah folder Http/ di root app/ masih diperlukan?
3. Bagaimana dengan app/Integration/ di root?
4. Apakah ada kode di luar modul yang mengakses namespace modul secara langsung?
5. Apakah Console modules memiliki dependency ke HR modules?
6. Apakah ada modul Console yang lebih prioritas dari HR?

## Persetujuan yang Diperlukan

- [x] Human Decision Gate untuk CRITICAL classification
- [ ] Approval untuk struktur target (28 modul)
- [ ] Approval untuk urutan pengerjaan
- [ ] Approval untuk prioritas Console vs HR

## Keterlacakan

- Terkait dengan docs/DDD-Lite-Modular-Monolith-Laravel-Acuan.md
- Terkait dengan docs/03-architecture/MODULE-CATALOG.md (perlu update)
- Terkait dengan docs/03-architecture/DEPENDENCY-RULES.md (perlu update)
