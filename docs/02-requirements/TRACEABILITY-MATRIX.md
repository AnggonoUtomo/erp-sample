# Traceability Matrix

## Overview

Matriks keterlacakan untuk menghubungkan requirement, design, implementation, dan testing.

## Requirement ke Design

| Requirement ID | Requirement | Design Element | Status |
|---|---|---|---|
| REQ-001 | 18 modul HR dan DocumentManagement | MODULE-CATALOG.md - 18 modules | Traced |
| REQ-002 | DDD-Lite structure | SYSTEM-DESIGN.md - DDD-Lite layers | Traced |
| REQ-003 | RBAC dengan Spatie | SYSTEM-DESIGN.md - Security architecture | Traced |
| REQ-004 | Audit trail | DATABASE-DESIGN.md - audit_log table | Traced |
| REQ-005 | Notifikasi dokumen expiry | SYSTEM-DESIGN.md - Event system | Traced |
| REQ-006 | Workflow onboarding | DATABASE-DESIGN.md - onboarding tables | Traced |
| REQ-007 | Workflow offboarding | DATABASE-DESIGN.md - offboarding tables | Traced |
| REQ-008 | Module generator | TECHNICAL-SPEC.md - Module structure | Traced |
| REQ-009 | Test coverage >85% | TESTING-STRATEGY.md - Coverage targets | Traced |
| REQ-010 | Inertia.js React frontend | TECHNICAL-SPEC.md - Tech stack | Traced |

## Design ke Implementation

| Design Element | Implementation | Status |
|---|---|---|
| DDD-Lite layers | app/Modules/{Boundary}/{Module}/ | Planned |
| Module generator | MakeModuleCommand | Existing |
| Employee table | hr_employees migration | Existing |
| Onboarding tables | hr_onboardings migration | Existing |
| Offboarding tables | hr_offboardings migration | Existing |
| Document tables | documents migration | Existing |
| RBAC | Spatie Permission | Existing |
| Event system | Laravel Events | Existing |

## Requirement ke Test

| Requirement ID | Requirement | Test Coverage | Status |
|---|---|---|---|
| REQ-001 | 18 modul | Module validation tests | Planned |
| REQ-002 | DDD-Lite structure | Architecture tests | Planned |
| REQ-003 | RBAC | Permission tests | Planned |
| REQ-004 | Audit trail | Audit log tests | Planned |
| REQ-005 | Notifikasi expiry | Notification tests | Planned |
| REQ-006 | Onboarding | Onboarding workflow tests | Planned |
| REQ-007 | Offboarding | Offboarding workflow tests | Planned |
| REQ-008 | Module generator | Generator tests | Planned |
| REQ-009 | Test coverage | Coverage report | Planned |
| REQ-010 | Frontend | Frontend tests | Planned |

## Module ke Database

| Module | Tables | Status |
|---|---|---|
| HR/WorkLocations | hr_work_locations | Existing |
| HR/Positions | hr_positions | Existing |
| HR/OrganizationStructures | hr_organization_structures | Existing |
| HR/Employees | hr_employees | Existing |
| HR/EmployeeContracts | hr_employee_contracts | Existing |
| HR/EmployeeDocuments | hr_employee_documents | Existing |
| HR/EmployeeMovements | hr_employee_movements | Existing |
| HR/EmploymentStatuses | hr_employment_statuses | Existing |
| HR/EmploymentTypes | hr_employment_types | Existing |
| HR/JobLevels | hr_job_levels | Existing |
| HR/Departements | hr_departements | Existing |
| HR/Onboardings | hr_onboardings, hr_onboarding_tasks, hr_onboarding_templates | Existing |
| HR/Offboardings | hr_offboardings, hr_offboarding_tasks, hr_offboarding_templates | Existing |
| DocumentManagement | documents, document_versions, document_approvals | Existing |

## Module ke Kontrak

| Module | Kontrak Publik | Status |
|---|---|---|
| HR/WorkLocations | WorkLocationLookup | Planned |
| HR/Positions | PositionLookup | Planned |
| HR/Employees | EmployeeLookup, EmployeeManagement | Planned |
| HR/EmployeeContracts | ContractLookup | Planned |
| HR/Onboardings | OnboardingService | Planned |
| HR/Offboardings | OffboardingService | Planned |
| HR/IntegrationContracts | EmployeeSnapshotProvider | Existing |
| DocumentManagement | DocumentService | Planned |

## Module ke Event

| Module | Event Dipublikasi | Status |
|---|---|---|
| HR/WorkLocations | WorkLocationCreated, WorkLocationUpdated | Existing |
| HR/Positions | PositionCreated | Planned |
| HR/Employees | EmployeeRegistered | Planned |
| HR/EmployeeContracts | ContractCreated, ContractExpiring | Planned |
| HR/EmployeeDocuments | DocumentUploaded, DocumentExpiring | Planned |
| HR/Onboardings | OnboardingCompleted | Planned |
| HR/Offboardings | OffboardingCompleted | Planned |
| HR/IntegrationContracts | HRIntegrationEventV1 | Existing |
| DocumentManagement | DocumentUploaded, DocumentApproved | Planned |

## Test Coverage Tracking

| Module | Unit Tests | Feature Tests | Integration Tests | Coverage |
|---|---|---|---|---|
| HR/WorkLocations | - | - | - | TBD |
| HR/Positions | - | - | - | TBD |
| HR/Employees | - | - | - | TBD |
| HR/Onboardings | - | - | - | TBD |
| HR/Offboardings | - | - | - | TBD |
| DocumentManagement | - | - | - | TBD |

## Change Tracking

| Change ID | Date | Description | Affected Modules | Status |
|---|---|---|---|---|
| ARC-DDD-LITE-001 | 2026-08-12 | Restrukturisasi DDD-Lite | All 18 modules | Proposed |
