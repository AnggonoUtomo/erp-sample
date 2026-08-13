---
id: MOD-CATALOG-001
title: Katalog Modul Aktif dan Target
document_type: architecture-catalog
status: active
version: 1.1.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-14
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002, DEP-HR-001]
---

# Katalog Modul Aktif dan Target

Katalog ini membedakan fakta implementasi saat ini dari keputusan target. `Dipertahankan` berarti tanggung jawabnya cukup mandiri; bukan klaim bahwa struktur foldernya telah memenuhi ADR-0001.

## Ringkasan

| Keadaan | Console | HR | DocumentManagement | Total |
|---|---:|---:|---:|---:|
| Manifest aktif pada kode | 11 | 16 | 1 | 28 |
| Target setelah deprecation yang diusulkan | 11 | 15 | 1 | 27 |

- Tidak ada penggabungan modul yang diusulkan dari evaluasi awal.
- Dua penggantian nama menjadi kandidat karena nama tidak menggambarkan bisnis dengan baik: `HR/Departements` dan `DocumentManagement/Foundation`.
- `HR/IntegrationContracts` diusulkan dihentikan sebagai shell modul teknis. Kontraknya tidak dihapus; ownership-nya dipindahkan sesuai ADR-0002.

## Console

| Modul saat ini | Tanggung jawab yang terlihat pada kode | Data utama | Evaluasi | Usulan |
|---|---|---|---|---|
| `AccessControls` | Pengelolaan role dan permission | tabel Spatie Permission | Mandiri sebagai kapabilitas akses | Dipertahankan |
| `ActivityCenters` | Pusat aktivitas/notifikasi dan aksi mark-as-read | read model/notifikasi framework | Kapabilitas aplikasi lintas sumber | Dipertahankan |
| `AuditLogs` | Pencatatan dan pembacaan audit | `audit_logs` | Mandiri dan sensitif kepatuhan | Dipertahankan |
| `BackupRestores` | Export, backup, dan restore | file/arsip backup | Kapabilitas operasional dengan risiko tersendiri | Dipertahankan |
| `GlobalSearches` | Pencarian command palette dan entity provider | tanpa tabel khusus | Read capability lintas modul | Dipertahankan |
| `LoginActivities` | Monitoring login dan percobaan autentikasi | `login_activities` | Kapabilitas keamanan | Dipertahankan |
| `NotificationTemplates` | Pengelolaan template notifikasi | `notification_templates` | Kapabilitas konfigurasi pesan | Dipertahankan |
| `QueueMonitors` | Monitoring dan operasi failed jobs | tabel queue Laravel | Kapabilitas operasional | Dipertahankan |
| `SchedulerMonitors` | Monitoring dan eksekusi scheduler | runtime scheduler | Kapabilitas operasional | Dipertahankan |
| `SystemSettings` | Konfigurasi email, branding, security, maintenance, dan lainnya | `system_settings` | Kapabilitas konfigurasi sistem | Dipertahankan |
| `UserManagements` | User, role assignment, avatar, dan impersonation | `users`, relasi role | Kapabilitas identitas administratif | Dipertahankan |

## HR

| Modul saat ini | Tanggung jawab yang terlihat pada kode | Data utama | Evaluasi | Usulan |
|---|---|---|---|---|
| `Departements` | Master departemen dan hierarki | `hr_departements` | Mandiri; nama salah eja | Dipertahankan, kandidat rename ke `Departments` |
| `EmployeeContracts` | Riwayat kontrak effective-dated dan terminasi kontrak | `hr_employee_contracts` | Lifecycle mandiri dan memiliki kontrak integrasi aktif | Dipertahankan |
| `EmployeeDocuments` | Metadata, compliance, verifikasi, dan attachment dokumen pegawai | `hr_employee_documents` | Ownership metadata HR berbeda dari storage DMS | Dipertahankan |
| `EmployeeMovements` | Perubahan assignment effective-dated, approval, apply, cancel, archive | `hr_employee_movements` | Lifecycle mandiri | Dipertahankan |
| `Employees` | Profil inti dan assignment aktif pegawai | `hr_employees` | Aggregate/master utama HR | Dipertahankan |
| `EmploymentStatuses` | Master status hubungan kerja | `hr_employment_statuses` | Referensi bisnis yang dipakai lifecycle | Dipertahankan |
| `EmploymentTypes` | Master tipe hubungan kerja | `hr_employment_types` | Referensi bisnis yang dipakai kontrak | Dipertahankan |
| `HRReferenceData` | Kategori dan nilai referensi umum HR | `hr_reference_categories`, `hr_reference_data` | Supporting business capability | Dipertahankan |
| `HRReports` | Query laporan HR read-only | tanpa tabel khusus | Read capability bisnis yang jelas | Dipertahankan |
| `IntegrationContracts` | Registry/snapshot/event contract teknis | tanpa tabel/route/use case bisnis | Tidak mandiri sebagai modul bisnis | Deprecation melalui `DEP-HR-001` |
| `JobLevels` | Master level/grade jabatan | `hr_job_levels` | Referensi bisnis | Dipertahankan |
| `Offboardings` | Template, checklist, readiness, dan finalisasi keluar | tabel `hr_offboarding_*` | Lifecycle mandiri | Dipertahankan |
| `Onboardings` | Template, checklist, assignment, dan penyelesaian masuk | tabel `hr_onboarding_*` | Lifecycle mandiri | Dipertahankan |
| `OrganizationStructures` | Node struktur formal dan reporting line | `hr_organization_structures` | Struktur formal berbeda dari master departemen/posisi | Dipertahankan |
| `Positions` | Master jabatan yang terkait departemen | `hr_positions` | Referensi bisnis | Dipertahankan |
| `WorkLocations` | Master lokasi kerja | `hr_work_locations` | Referensi bisnis; pilot struktur ADR-0001 terverifikasi | Dipertahankan |

## DocumentManagement

| Modul saat ini | Tanggung jawab yang terlihat pada kode | Data utama | Evaluasi | Usulan |
|---|---|---|---|---|
| `Foundation` | Ingestion, versioning, archive/restore, secure delivery, dan access decision | `dm_documents`, `dm_document_versions`, `dm_idempotency_keys`, `dm_delivery_tokens` | Kapabilitas bisnis nyata; nama/description “contract-only foundation” tidak sesuai perilaku | Dipertahankan, kandidat rename ke `Documents` |

## Aturan Evaluasi

1. Modul tidak digabung hanya karena menggunakan tabel atau framework yang sama.
2. Modul dapat tetap ada tanpa tabel bila memiliki use case/read capability/risiko operasional yang mandiri.
3. Modul yang hanya mengelompokkan kontrak teknis tidak dianggap kapabilitas bisnis.
4. Rename atau merge tidak boleh mengubah route, permission, data, contract semantics, atau hasil use case.
5. Kandidat rename membutuhkan work item dan compatibility plan sebelum coding.

## Sumber Bukti

- `app/Modules/*/*/module.php`
- migration pada `app/Modules/**/Database/Migrations/` dan `database/migrations/`
- route dan service provider tiap modul
- pencarian import namespace lintas modul pada 2026-08-13

Katalog historis sebelum SEOS tersedia melalui `BL-2026-001-pre-seos` dan tidak menjadi sumber kebenaran aktif.

## Status Struktur Incremental

Pada 2026-08-14, `HR/WorkLocations` menjadi modul pertama yang terverifikasi memakai struktur minimal ADR-0001. Fakta ini tidak mengubah jumlah, nama, tanggung jawab, data ownership, atau usulan deprecation modul. Modul lain tetap dievaluasi/migrasikan melalui work item terpisah berdasarkan readiness dan dependency order.
