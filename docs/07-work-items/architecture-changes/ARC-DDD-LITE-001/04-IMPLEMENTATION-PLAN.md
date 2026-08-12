# 04 Rencana Implementasi

> Fokus wajib: irisan perubahan arsitektur yang reversible, kompatibilitas bridge, pengujian, dan rollback.

## Metadata

```yaml
work_item: ARC-DDD-LITE-001
status: draft
owner: unassigned
last_updated: 2026-08-12
```

## Tujuan

Merencanakan implementasi restrukturisasi DDD-Lite secara incremental dengan irisan yang reversible dan rollback yang aman.

## Input dan Referensi

- docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/01-DISCOVERY-RECORD.md
- docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/02-BOUNDARY-PROPOSAL.md
- docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/03-IMPACT-ASSESSMENT.md
- app/Support/Modules/Commands/MakeModuleCommand.php

## Detail

### Phase 1: Module Generator Update (Priority 1)

**Tujuan:** Update MakeModuleCommand untuk generate struktur DDD-Lite.

**Task:**
1. Update `makeDirectories()` method untuk generate folder DDD-Lite
2. Update semua stub files untuk namespace baru
3. Update controller stub untuk path Presentation/Controllers
4. Update service stub untuk path Application/Services
5. Update provider stub untuk path Infrastructure/Providers
6. Tambah stub untuk Action class (Application/Actions)
7. Tambah stub untuk Policy class (Presentation/Policies)

**Struktur generator baru:**
```
Application/
  Actions/
  DTO/
  Queries/
  Services/
  Contracts/
Domain/
  Events/
  Exceptions/
  Services/
  ValueObjects/
Infrastructure/
  Persistence/
    Models/
    Repositories/
  Observers/
  Providers/
  External/
Presentation/
  Controllers/
  Policies/
  Requests/
  Resources/
Integration/
  Contracts/
  Listeners/
  Services/
Database/
  Migrations/
  Factories/
  Seeders/
Routes/
  web.php
  api.php
  console.php
Tests/
  Feature/
  Unit/
```

**Verifikasi:**
- [ ] `php artisan make:module HR/TestModule` generate struktur DDD-Lite
- [ ] Namespace di semua stub benar
- [ ] ServiceProvider bisa di-load
- [ ] Routes bisa diakses

---

### Phase 2: Shared Kernel & Foundation (Priority 2)

**Tujuan:** Pastikan Shared Kernel dan foundation classes siap.

**Task:**
1. Review `app/Shared/` structure
2. Update ValueObjects jika perlu:
   - Money.php
   - DateRange.php
   - PhoneNumber.php
   - EmailAddress.php
3. Update base classes:
   - BaseDomainEvent.php
   - DomainEventDispatcher.php
   - DataObject.php

**Verifikasi:**
- [ ] Shared Kernel namespace tidak berubah (backward compatible)
- [ ] Semua modul bisa akses Shared Kernel

---

### Phase 3: HR/WorkLocations - Modul Percontohan (Priority 3)

**Tujuan:** Konversi modul WorkLocations sebagai proof of concept.

**Task:**
1. Buat folder struktur DDD-Lite di WorkLocations
2. Pindahkan files sesuai mapping:
   - Models/ → Infrastructure/Persistence/Models/
   - Services/ → Application/Services/
   - Http/Controllers/ → Presentation/Controllers/
   - Http/Requests/ → Presentation/Requests/
   - Policies/ → Presentation/Policies/
   - Providers/ → Infrastructure/Providers/
   - DTO/ → Application/DTO/
   - Events/ → Domain/Events/
   - Listeners/ → Integration/Listeners/
   - Integrations/ → Integration/
3. Update namespace di semua files
4. Update ServiceProvider path
5. Pindahkan routes.php ke Routes/web.php
6. Update autoloader
7. Jalankan tests

**Verifikasi:**
- [ ] `composer dump-autoload` berhasil
- [ ] `php artisan route:list` routes WorkLocations terdaftar
- [ ] `php artisan test --filter=HRWorkLocation` semua test pass
- [ ] Aplikasi bisa diakses di browser

---

### Phase 4: HR/Positions & HR/OrganizationStructures (Priority 4)

**Tujuan:** Konversi modul Positions dan OrganizationStructures.

**Task:**
1. Ulangi proses Phase 3 untuk Positions
2. Ulangi proses Phase 3 untuk OrganizationStructures
3. Update dependency references ke WorkLocations (jika ada)

**Verifikasi:**
- [ ] Tests pass untuk Positions dan OrganizationStructures
- [ ] Dependency ke WorkLocations tidak putus

---

### Phase 5: HR/Employees & Modul Terkait (Priority 5)

**Tujuan:** Konversi modul Employees dan modul yang bergantung padanya.

**Task:**
1. Konversi Employees
2. Konversi EmployeeContracts
3. Konversi EmployeeDocuments
4. Konversi EmployeeMovements
5. Konversi EmploymentStatuses
6. Konversi EmploymentTypes

**Verifikasi:**
- [ ] Semua tests pass
- [ ] Integration contracts masih berfungsi

---

### Phase 6: HR/Onboardings & HR/Offboardings (Priority 6)

**Tujuan:** Konversi modul Onboardings dan Offboardings.

**Task:**
1. Konversi Onboardings
2. Konversi Offboardings
3. Update dependency ke Employees dan OrganizationStructures

**Verifikasi:**
- [ ] Semua tests pass
- [ ] Lifecycle events masih berfungsi

---

### Phase 7: HR/JobLevels, HR/Departements, HR/HRReferenceData, HR/HRReports (Priority 7)

**Tujuan:** Konversi modul HR sisanya.

**Task:**
1. Konversi JobLevels
2. Konversi Departements
3. Konversi HRReferenceData
4. Konversi HRReports

**Verifikasi:**
- [ ] Semua tests pass

---

### Phase 8: HR/IntegrationContracts (Priority 8)

**Tujuan:** Konversi modul IntegrationContracts.

**Task:**
1. Konversi IntegrationContracts
2. Update semua kontrak snapshot provider
3. Update event registry

**Verifikasi:**
- [ ] Integration contracts masih bisa diakses dari DocumentManagement
- [ ] Snapshot providers masih berfungsi

---

### Phase 9: DocumentManagement (Priority 9)

**Tujuan:** Konversi modul DocumentManagement.

**Task:**
1. Konversi DocumentManagement
2. Update dependency ke HR IntegrationContracts

**Verifikasi:**
- [ ] Semua tests pass
- [ ] Integrasi dengan HR masih berfungsi

---

### Phase 10: Tests Migration & Cleanup (Priority 10)

**Tujuan:** Migrasi semua tests ke dalam modul dan cleanup.

**Task:**
1. Pindahkan tests dari `tests/Feature/` ke `app/Modules/{Boundary}/{Module}/Tests/Feature/`
2. Pindahkan tests dari `tests/Unit/` ke `app/Modules/{Boundary}/{Module}/Tests/Unit/`
3. Update namespace di semua test files
4. Update `phpunit.xml` untuk include test paths di modul
5. Hapus folder lama yang sudah tidak digunakan
6. Update dokumentasi

**Verifikasi:**
- [ ] `php artisan test` semua tests pass
- [ ] Tidak ada file duplikat
- [ ] Dokumentasi terupdate

---

### Rollback Strategy

Jika ada masalah kritis di phase manapun:

1. **Stop** pekerjaan di kondisi repository yang valid
2. **Catat** deviasi di DEVIATION-RECORD.md
3. **Git revert** ke commit sebelum phase tersebut
4. **Investigasi** akar masalah
5. **Update** rencana jika perlu
6. **Lanjut** setelah masalah terselesaikan

## Keputusan / Hasil

1. **10 phases** dengan urutan dari generator ke modul paling dependen
2. **Setiap phase** harus lulus verifikasi sebelum lanjut
3. **Rollback** per phase, bukan keseluruhan
4. **Tests** adalah gate keeper - tidak lanjut jika tests gagal

## Risiko dan Pertanyaan Terbuka

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Phase 3 (WorkLocations) gagal | HIGH | Proof of concept, investigasi mendalam sebelum lanjut |
| Dependency putus antar modul | HIGH | Test dependency setelah setiap phase |
| Tests gagal setelah namespace change | MEDIUM | Update namespace secara sistematis |
| Routes tidak ter-register | MEDIUM | Verify route:list setelah setiap phase |

## Persetujuan yang Diperlukan

- [ ] Human Decision Gate untuk implementation plan
- [ ] Approval untuk 10-phase approach
- [ ] Approval untuk rollback strategy

## Keterlacakan

- Terkait dengan docs/06-planning/IMPLEMENTATION-PLAN.md
- Terkait dengan docs/05-engineering/TESTING-STRATEGY.md

</contents>