---
id: DB-DESIGN-001
title: Baseline Kepemilikan Database Aktual
document_type: data-design
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [MIG-ID-001, ADR-0001]
---

# Baseline Kepemilikan Database Aktual

## Strategi Identifier Saat Ini

Migration aplikasi memakai `$table->id()` dan `foreignId()` untuk primary/foreign key utama. Artinya baseline saat ini adalah integer/bigint auto-increment, bukan ULID `CHAR(26)`.

ULID hanya ditemukan untuk sebagian storage object key DMS dan bukan primary key relasional. Perubahan primary key ke ULID dipisahkan ke `MIG-ID-001`, berstatus `deferred`, dan tidak termasuk restrukturisasi DDD-Lite.

## Kepemilikan Tabel Modul

| Modul pemilik | Tabel yang dibuktikan migration |
|---|---|
| `Console/UserManagements` | `users`, `password_reset_tokens`, `sessions` |
| `Console/AccessControls` | `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` |
| `Console/AuditLogs` | `audit_logs` |
| `Console/LoginActivities` | `login_activities` |
| `Console/NotificationTemplates` | `notification_templates` |
| `Console/SystemSettings` | `system_settings` |
| Laravel infrastructure | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `media` |
| `HR/Departements` | `hr_departements` |
| `HR/JobLevels` | `hr_job_levels` |
| `HR/WorkLocations` | `hr_work_locations` |
| `HR/EmploymentStatuses` | `hr_employment_statuses` |
| `HR/EmploymentTypes` | `hr_employment_types` |
| `HR/HRReferenceData` | `hr_reference_categories`, `hr_reference_data` |
| `HR/Positions` | `hr_positions` |
| `HR/OrganizationStructures` | `hr_organization_structures` |
| `HR/Employees` | `hr_employees` |
| `HR/EmployeeContracts` | `hr_employee_contracts` |
| `HR/EmployeeDocuments` | `hr_employee_documents` |
| `HR/EmployeeMovements` | `hr_employee_movements` |
| `HR/Onboardings` | `hr_onboarding_templates`, `hr_onboarding_template_items`, `hr_onboardings`, `hr_onboarding_tasks` |
| `HR/Offboardings` | `hr_offboarding_templates`, `hr_offboarding_template_items`, `hr_offboardings`, `hr_offboarding_tasks` |
| `DocumentManagement/Foundation` | `dm_documents`, `dm_idempotency_keys`, `dm_document_versions`, `dm_delivery_tokens` |

## Koreksi terhadap Baseline Lama

- Tabel DMS memakai prefix `dm_`; tidak ada bukti tabel `documents`, `document_versions`, atau `document_approvals` pada migration aktif.
- Employee Documents menyimpan referensi logis DMS sesuai kontrak; ia tidak mengambil alih ownership storage/version.
- `HR/IntegrationContracts`, `HR/HRReports`, dan beberapa modul Console read/operational tidak memiliki tabel sendiri. Ketiadaan tabel tidak otomatis berarti modul tidak valid.

## Aturan

1. Hanya modul pemilik yang melakukan mutation terhadap tabelnya.
2. Foreign key lintas modul tidak mengalihkan ownership.
3. Perubahan type identifier, key, atau constraint memerlukan work item data migration dan rollback plan.
4. Detail kolom authoritative berada pada migration sampai schema snapshot otomatis tersedia.
