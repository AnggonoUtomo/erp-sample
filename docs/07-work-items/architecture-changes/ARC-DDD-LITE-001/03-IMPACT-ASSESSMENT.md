# 03 Penilaian Dampak

> Fokus wajib: requirement, module, data, API, event, keamanan, rollout, dan dampak dokumentasi.

## Metadata

```yaml
work_item: ARC-DDD-LITE-001
status: draft
owner: unassigned
last_updated: 2026-08-12
```

## Tujuan

Menilai dampak restrukturisasi DDD-Lite terhadap modul, data, API, event, keamanan, dan dokumentasi.

## Input dan Referensi

- docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/01-DISCOVERY-RECORD.md
- docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/02-BOUNDARY-PROPOSAL.md
- app/Modules/ - Struktur saat ini

## Detail

### Dampak terhadap Modul

| Modul | Dampak | Tingkat | Catatan |
|---|---|---|---|
| HR/WorkLocations | Namespace changes, folder restructure | HIGH | Modul pertama yang dikonversi |
| HR/Positions | Namespace changes, folder restructure | HIGH | Dependency ke WorkLocations |
| HR/OrganizationStructures | Namespace changes, folder restructure | HIGH | Dependency ke Positions |
| HR/Employees | Namespace changes, folder restructure | HIGH | Dependency ke WorkLocations, Positions |
| HR/EmployeeContracts | Namespace changes, folder restructure | HIGH | Dependency ke Employees |
| HR/EmployeeDocuments | Namespace changes, folder restructure | HIGH | Dependency ke Employees |
| HR/EmployeeMovements | Namespace changes, folder restructure | HIGH | Dependency ke Employees, Positions |
| HR/EmploymentStatuses | Namespace changes, folder restructure | MEDIUM | Dependency ke Employees |
| HR/EmploymentTypes | Namespace changes, folder restructure | MEDIUM | Dependency ke Employees |
| HR/JobLevels | Namespace changes, folder restructure | MEDIUM | Dependency ke Positions |
| HR/Departements | Namespace changes, folder restructure | MEDIUM | Dependency ke OrganizationStructures |
| HR/HRReferenceData | Namespace changes, folder restructure | LOW | Standalone |
| HR/HRReports | Namespace changes, folder restructure | HIGH | Dependency ke multiple HR modules |
| HR/Onboardings | Namespace changes, folder restructure | HIGH | Dependency ke Employees, OrganizationStructures |
| HR/Offboardings | Namespace changes, folder restructure | HIGH | Dependency ke Employees, Onboardings |
| HR/IntegrationContracts | Namespace changes, folder restructure | HIGH | Kontrak lintas modul |
| DocumentManagement | Namespace changes, folder restructure | HIGH | Dependency ke HR IntegrationContracts |
| Console | Namespace changes | LOW | Minimal structure |

### Dampak terhadap Namespace

**Perubahan namespace utama:**

| Namespace Lama | Namespace Baru |
|---|---|
| App\Modules\HR\WorkLocations\Models\WorkLocation | App\Modules\HR\WorkLocations\Infrastructure\Persistence\Models\WorkLocation |
| App\Modules\HR\WorkLocations\Services\WorkLocationsService | App\Modules\HR\WorkLocations\Application\Services\WorkLocationsService |
| App\Modules\HR\WorkLocations\Http\Controllers\WorkLocationsController | App\Modules\HR\WorkLocations\Presentation\Controllers\WorkLocationsController |
| App\Modules\HR\WorkLocations\DTO\WorkLocationData | App\Modules\HR\WorkLocations\Application\DTO\WorkLocationData |
| App\Modules\HR\WorkLocations\Events\WorkLocationCreated | App\Modules\HR\WorkLocations\Domain\Events\WorkLocationCreated |
| App\Modules\HR\WorkLocations\Policies\WorkLocationPolicy | App\Modules\HR\WorkLocations\Presentation\Policies\WorkLocationPolicy |
| App\Modules\HR\WorkLocations\Providers\WorkLocationsServiceProvider | App\Modules\HR\WorkLocations\Infrastructure\Providers\WorkLocationsServiceProvider |

### Dampak terhadap Tests

**Tests yang perlu diupdate:**
- tests/Feature/HRWorkLocationTest.php → app/Modules/HR/WorkLocations/Tests/Feature/
- tests/Feature/HRPositionTest.php → app/Modules/HR/Positions/Tests/Feature/
- tests/Feature/HREmployeeTest.php → app/Modules/HR/Employees/Tests/Feature/
- tests/Feature/HROnboardingTest.php → app/Modules/HR/Onboardings/Tests/Feature/
- tests/Feature/HROffboardingTest.php → app/Modules/HR/Offboardings/Tests/Feature/
- tests/Feature/DocumentManagementTest.php → app/Modules/DocumentManagement/Tests/Feature/
- Dan sekitar 50+ test files lainnya

### Dampak terhadap Routes

**Routes yang perlu dipindah:**
- routes/web.php → app/Modules/{Boundary}/{Module}/Routes/web.php
- routes/api.php → app/Modules/{Boundary}/{Module}/Routes/api.php
- routes/console.php → app/Modules/{Boundary}/{Module}/Routes/console.php

### Dampak terhadap Module Generator

**MakeModuleCommand perlu diupdate untuk:**
- Generate struktur DDD-Lite layered
- Update namespace stubs
- Update provider path
- Update controller path
- Update service path

### Dampak terhadap Autoloading

**composer.json perlu diupdate:**
- PSR-4 autoload paths mungkin perlu penyesuaian
- Run `composer dump-autoload` setelah setiap perubahan namespace

### Dampak terhadap ServiceProvider

**ModuleServiceProvider perlu diupdate untuk:**
- Load routes dari lokasi baru
- Register bindings dari namespace baru
- Load migrations dari lokasi baru

### Dampak terhadap Kontrak Lintas Modul

**HR IntegrationContracts:**
- EmployeeSnapshotProvider interface
- EmployeeContractSnapshotProvider interface
- EmployeeDocumentComplianceSnapshotProvider interface
- EmployeeAssignmentSnapshotProvider interface

Konsumen di DocumentManagement perlu update namespace.

### Dampak terhadap Frontend

**Inertia pages:**
- Path reference di controller perlu diupdate
- Contoh: `Inertia::render('hr/work-locations/index')` tetap sama karena frontend path tidak berubah

### Dampak terhadap Keamanan

**Policies:**
- Namespace changes di semua policy files
- Gate definitions mungkin perlu update
- Permission registry perlu update

### Dampak terhadap Dokumentasi

**Dokumen yang perlu diupdate:**
- docs/03-architecture/MODULE-CATALOG.md
- docs/03-architecture/BOUNDARY-REGISTRY.md
- docs/03-architecture/DEPENDENCY-RULES.md
- docs/04-design/EVENT-CATALOG.md
- docs/DDD-Lite-Modular-Monolith-Laravel-Acuan.md (jika ada perubahan)

## Keputusan / Hasil

1. **Urutan konversi**: Generator → Shared → Modul dengan dependency banyak → Modul dependen
2. **Backward compatibility**: Tidak ada backward compatibility layer, langsung cut-over
3. **Testing strategy**: Test suite harus pass setelah setiap modul dikonversi
4. **Rollback**: Git revert jika ada masalah kritis

## Risiko dan Pertanyaan Terbuka

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Namespace changes memutus autoloading | HIGH | Update composer.json, run composer dump-autoload |
| Cross-module references hardcode namespace lama | HIGH | Grep search semua namespace lama sebelum move |
| Tests gagal setelah move | MEDIUM | Update namespace di tests, verify satu per satu |
| Routes tidak ter-register | MEDIUM | Pastikan ServiceProvider load routes dari lokasi baru |
| Frontend Inertia pages tidak ketemu | LOW | Frontend path tidak berubah |

## Persetujuan yang Diperlukan

- [x] Human Decision Gate untuk CRITICAL classification
- [ ] Approval untuk impact assessment
- [ ] Approval untuk rollback strategy

## Keterlacakan

- Terkait dengan docs/03-architecture/MODULE-CATALOG.md
- Terkait dengan docs/03-architecture/DEPENDENCY-RULES.md
- Terkait dengan docs/04-design/EVENT-CATALOG.md

</contents>