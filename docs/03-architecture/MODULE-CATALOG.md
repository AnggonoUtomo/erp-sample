# Katalog Module

| Module | Boundary | Tujuan | Owns Data | Kontrak Publik | Menerbitkan Event | Mengonsumsi | Dilarang Dependensi | Pemilik |
|---|---|---|---|---|---|---|---|---|
| WorkLocations | HR | Master lokasi kerja | hr_work_locations | WorkLocationLookup | WorkLocationCreated | - | Direct employee update | HR Team |
| Positions | HR | Manajemen posisi/jabatan | hr_positions | PositionLookup | PositionCreated | WorkLocations | Direct org structure update | HR Team |
| OrganizationStructures | HR | Struktur organisasi | hr_organization_structures | OrgStructureLookup | OrgStructureCreated | Positions | Direct position update | HR Team |
| Employees | HR | Data karyawan | hr_employees | EmployeeLookup, EmployeeManagement | EmployeeRegistered | WorkLocations, Positions | Direct contract update | HR Team |
| EmployeeContracts | HR | Kontrak karyawan | hr_employee_contracts | ContractLookup | ContractCreated, ContractExpiring | Employees | Direct employee update | HR Team |
| EmployeeDocuments | HR | Dokumen karyawan | hr_employee_documents | DocumentLookup | DocumentUploaded, DocumentExpiring | Employees | Direct document access | HR Team |
| EmployeeMovements | HR | Mutasi karyawan | hr_employee_movements | MovementLookup | MovementCreated | Employees, Positions | Direct position update | HR Team |
| EmploymentStatuses | HR | Status employment | hr_employment_statuses | StatusLookup | StatusChanged | Employees | Direct employee update | HR Team |
| EmploymentTypes | HR | Tipe employment | hr_employment_types | TypeLookup | TypeChanged | Employees | Direct employee update | HR Team |
| JobLevels | HR | Level jabatan | hr_job_levels | JobLevelLookup | JobLevelChanged | Positions | Direct position update | HR Team |
| Departements | HR | Departemen | hr_departements | DepartmentLookup | DepartmentCreated | OrganizationStructures | Direct org update | HR Team |
| HRReferenceData | HR | Data referensi HR | hr_reference_data | ReferenceLookup | - | - | Direct master data update | HR Team |
| HRReports | HR | Reporting HR | - | ReportService | - | All HR modules | Direct data modification | HR Team |
| Onboardings | HR | Workflow onboarding | hr_onboardings, hr_onboarding_tasks, hr_onboarding_templates | OnboardingService | OnboardingCompleted | Employees, OrganizationStructures | Direct employee activation | HR Team |
| Offboardings | HR | Workflow offboarding | hr_offboardings, hr_offboarding_tasks, hr_offboarding_templates | OffboardingService | OffboardingCompleted | Employees, Onboardings | Direct employee deactivation | HR Team |
| IntegrationContracts | HR | Kontrak integrasi | - | EmployeeSnapshotProvider, ContractSnapshotProvider | HRIntegrationEventV1 | All HR modules | Direct data modification | HR Team |
| DocumentManagement | DocumentManagement | Manajemen dokumen | documents, document_versions, document_approvals | DocumentService | DocumentUploaded, DocumentApproved | HR IntegrationContracts | Direct HR data update | Admin Team |
| UserManagements | Console | User management | users, role_has_permissions | UserService | UserCreated, UserImpersonated | - | Direct role modification | System Team |
| SystemSettings | Console | System configuration | system_settings | SettingService | SettingChanged | - | Direct config modification | System Team |
| AuditLogs | Console | Audit trail | audit_logs | AuditLogService | - | All modules events | Direct log modification | System Team |
| BackupRestores | Console | Database backup | backup_history | BackupService | BackupCompleted | SystemSettings | Direct DB access | System Team |
| AccessControls | Console | Access control | access_policies | AccessControlService | AccessPolicyChanged | Users | Direct permission modification | System Team |
| ActivityCenters | Console | Activity tracking | activities | ActivityService | - | All modules | Direct activity modification | System Team |
| GlobalSearches | Console | Cross-module search | - | SearchService | - | All modules | Direct data modification | System Team |
| LoginActivities | Console | Login monitoring | login_activities | LoginActivityService | LoginDetected | - | Direct log modification | System Team |
| NotificationTemplates | Console | Notification templates | notification_templates | TemplateService | TemplateChanged | All modules | Direct template modification | System Team |
| QueueMonitors | Console | Queue monitoring | - | QueueMonitorService | - | Queue system | Direct queue modification | System Team |
| SchedulerMonitors | Console | Scheduled task monitoring | - | SchedulerMonitorService | - | Scheduler | Direct schedule modification | System Team |

## Boundary Aturan

- Modul dilarang query modul lain boundary privat tabel secara langsung kecuali secara eksplisit disetujui.
- Lintas-modul sinkron behavior menggunakan eksplisit publik kontrak.
- Lintas-modul asinkron behavior menggunakan terdokumentasi events/messages.
- Shared kode wajib minimal dan dilarang memuat spesifik modul aturan bisnis.

## Module Summary by Boundary

### HR Boundary (16 modules)
Core HR management: employee data, onboarding/offboarding, contracts, documents, organization structure.

### Console Boundary (11 modules)
System administration: user management, system settings, audit logs, backup/restore, access control, activity tracking, search, notifications, monitoring.

### DocumentManagement Boundary (1 module)
Document management with version control and approval workflow.

## Total: 28 Modules
