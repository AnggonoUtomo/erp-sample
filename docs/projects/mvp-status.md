# MVP Status Project

Dokumen ini menjadi peta ringkas status MVP project `laravel12-erp` per 26 Juli 2026.
Tujuannya bukan menggantikan specification/tasks per modul, tetapi menjadi indeks cepat untuk menentukan pekerjaan berikutnya.

## Ringkasan Eksekutif

Project sudah memiliki fondasi MVP yang cukup kuat untuk area:

- Console sebagai pusat identitas, konfigurasi, observability, runtime operation, recovery, dan global search.
- HR sebagai master data employee dan lifecycle employee.
- Document Management sebagai storage engine private tunggal.
- HR Integration Contracts sebagai boundary read-only untuk consumer masa depan seperti Attendance dan Payroll.

Project besar berikutnya seperti Attendance, Payroll, Accounting, CRM, dan Document Management lanjutan tetap harus memakai contract yang sudah ada, bukan membaca internal table HR secara langsung.

## Branch dan Alur Kerja

Status branch saat ini:

- `main`: branch utama/stabil.
- `dev`: branch kerja harian.
- `origin/HEAD`: mengarah ke `origin/main`.

Alur yang disepakati:

1. Kerja harian dilakukan di `dev`.
2. Commit dibuat per increment yang selesai.
3. Setelah stabil, perubahan dari `dev` dipindahkan ke `main`.
4. `main` dijaga sebagai branch yang siap dijadikan baseline stabil.

## Status Project Utama

| Project | Status | Ringkasan | Link |
|---|---|---|---|
| Console | MVP complete | Shell, access control, user management, settings, notification templates, activity center, audit logs, login activities, queue/scheduler monitor, backup restore, dan global search sudah tersedia. | [Console](console/README.md) |
| HR | MVP core complete | Master data HR, employees, contracts, documents, movements, onboardings, offboardings, reports, dan integration contracts sudah tersedia. | [HR](hr/README.md) |
| Document Management | Secure foundation complete | Private single-server storage, upload policy, staged ingestion, immutable version, archive/restore descriptor, access decision, secure delivery, dan HR adapter sudah tersedia. | [Document Management](document-management/README.md) |
| Attendance | Planning only | Belum masuk implementasi; harus memakai HR Integration Contracts saat mulai. | [Attendance roadmap](attendance/roadmap.md) |
| Payroll | Planning only | Belum masuk implementasi; harus memakai HR Integration Contracts dan nantinya Attendance snapshot. | [Payroll roadmap](payroll/roadmap.md) |
| Accounting | Planning only | Belum masuk implementasi; nantinya menerima journal/payroll summary, bukan detail personal employee. | [Accounting roadmap](accounting/roadmap.md) |
| CRM | Planning only | Belum masuk implementasi. | [CRM roadmap](crm/roadmap.md) |

## Status Console

| Area | Status | Catatan |
|---|---|---|
| Console shell | Done | Dashboard, layout, sidebar, header, theme, auth props, dan navigation baseline sudah terdokumentasi. |
| Access Control | Done | Role/permission protected boundary tersedia, termasuk role `super-system`. |
| User Management | Done | Lifecycle user, avatar, activation/reset link, role/direct permission, archive/restore, dan impersonation tersedia. |
| System Settings | Done | Branding, locale, email, map, maintenance, security, password policy, dan delete-account visibility tersedia. |
| Notification Templates | Done | Template lifecycle tersedia; preview/sending lanjutan masih bisa dipoles nanti. |
| Activity Center | Done | Read model dari audit log plus marker baca user. |
| Audit Logs | Done | User-facing route read-only; sanitizer secret recursive sudah tersedia. |
| Login Activities | Done | Login sukses/gagal/logout tercatat tanpa password/token. |
| Queue Monitor | Done | Runtime monitoring dan control berbasis permission. |
| Scheduler Monitor | Done | Runtime monitoring dan control berbasis permission. |
| Backup & Restore | Done | Signed recovery boundary, dry-run, dan runbook tersedia. |
| Global Search | Done | Command palette navigation search permission-aware tersedia; entity provider contract draft tersedia untuk pengembangan berikutnya. |

Referensi:

- [Console tasks](console/tasks.md)
- [Console final checkpoint](console/final-quality-checkpoint.md)
- [Global Search tasks](console/global-search/tasks.md)
- [Global Search final checkpoint](console/global-search/final-quality-checkpoint.md)

## Status HR

### HR foundation/master data

| Modul | Status | Catatan |
|---|---|---|
| Departements | Available | Master departement dengan soft delete dan dokumentasi project. |
| Positions | Available | Position terkait departement. |
| Job Levels | Available | Grade/level jabatan lintas departement. |
| Work Locations | Available | Lokasi kerja, koordinat, dan geofence radius. |
| Employment Statuses | Available | Status kerja dengan flag attendance, payroll, dan terminal. |
| Employment Types | Available | Tipe kerja dengan flag kontrak, payroll, benefit, dan overtime. |
| HR Reference Data | Available | Referensi kategori HR seperti gender, marital status, education, religion, bank, blood type. |
| Organization Structures | Available | Hierarchy organisasi terpisah dari departement. |

Referensi:

- [HR roadmap](hr/roadmap.md)
- [HR module guide](hr/module-guide.md)
- [Departments docs](hr/departements/README.md)
- [Positions docs](hr/positions/README.md)
- [Job Levels docs](hr/job-levels/README.md)
- [Work Locations docs](hr/work-locations/README.md)
- [Employment Statuses docs](hr/employment-statuses/README.md)
- [Employment Types docs](hr/employment-types/README.md)
- [HR Reference Data docs](hr/hr-reference-data/README.md)
- [Organization Structures docs](hr/organization-structures/README.md)

### HR employee lifecycle

| Modul | Status | Catatan |
|---|---|---|
| Employees | Available | Employee profile menjadi master data utama; masih bisa diperdalam per sub-profile jika diperlukan. |
| Employee Contracts | MVP complete | Effective-dated contract lifecycle tersedia. |
| Employee Documents | MVP complete | Metadata dokumen HR tersedia dan terhubung ke DMS tanpa membuat storage engine ganda. |
| Employee Movements | MVP complete | Transfer/status/type/assignment changes, future-effective scheduler, archive/restore, approval, dan event snapshot tersedia. |
| Onboardings | MVP complete | Template checklist, draft snapshot, activation, assignment, completion, cancel, archive/restore, dan overdue command tersedia. |
| Offboardings | MVP complete | Template checklist, draft snapshot, activation, task lifecycle, ready/finalize/cancel, employment termination boundary, due command, dan integration event tersedia. |
| HR Reports | MVP complete | Headcount, contract expiry, document expiry, read-only commands, lifecycle seeder, dan frontend polish tersedia. |
| HR Integration Contracts | MVP complete | Snapshot provider, DTO allowlist, forbidden-field guard, event envelope, read-only inspection command, dan consumer handoff tersedia. |

Referensi:

- [Employees docs](hr/employees/README.md)
- [Employee Contracts docs](hr/employee-contracts/README.md)
- [Employee Documents docs](hr/employee-documents/README.md)
- [Employee Movements docs](hr/employee-movements/README.md)
- [Onboardings docs](hr/onboardings/README.md)
- [Offboardings docs](hr/offboardings/README.md)
- [HR Reports docs](hr/hr-reports/README.md)
- [HR Integration Contracts docs](hr/integration-contracts/README.md)
- [Consumer handoff Attendance/Payroll](hr/integration-contracts/consumer-handoff.md)

## Status Document Management

| Area | Status | Catatan |
|---|---|---|
| Module boundary | Done | DMS menjadi storage engine private tunggal, bukan storage tambahan per module. |
| Logical metadata | Done | Metadata dokumen logical tersedia. |
| Private StorageAdapter | Done | Contract penyimpanan private tersedia. |
| Upload policy | Done | Maksimum 20 MB, allowlist `pdf/jpg/jpeg/png`, MIME dan magic-byte wajib cocok, polyglot ditolak. |
| Malware scanner | Deferred | MVP development dan production tanpa scanner; file tetap private dan akses lewat controller terotorisasi. |
| Staged ingestion | Done | Upload staged sebelum commit final. |
| Immutable replacement version | Done | Replacement menghasilkan versi baru, bukan overwrite. |
| Archive/restore descriptor | Done | Descriptor dan restore boundary tersedia. |
| Access decision matrix | Done | Keputusan akses terdokumentasi. |
| Secure delivery | Done | One-time secure delivery handoff tersedia. |
| HR attachment adapter | Done | HR Employee Documents memakai DMS reference contract. |

Referensi:

- [Document Management tasks](document-management/tasks.md)
- [Single private storage engine ADR](document-management/decisions/001-single-private-storage-engine.md)
- [Upload security policy ADR](document-management/decisions/002-upload-security-policy.md)
- [MVP without malware scanner ADR](document-management/decisions/003-mvp-single-server-without-malware-scanner.md)

## Quality Gate Terakhir yang Tercatat

Quality gate final terakhir yang terdokumentasi pada rangkaian Console Global Search:

- `npm run format:check`
- `npm run lint:check`
- `npm run typecheck`
- `npm run test:frontend`
- `npm run build`
- `php artisan module:validate`
- `php artisan test`
- `git diff --check`

Status: hijau pada checkpoint terkait.

Catatan: setiap perubahan baru tetap wajib menjalankan gate proporsional sesuai area yang disentuh. Jika menyentuh backend + frontend, jalankan gate penuh sebelum commit/push.

## Modul yang Masih Documentation-only atau Deferred

| Area | Status | Arahan |
|---|---|---|
| Attendance | Deferred | Mulai setelah HR Integration Contracts dipakai sebagai input resmi. |
| Payroll | Deferred | Mulai setelah Attendance/HR snapshot contract jelas. |
| Accounting | Deferred | Jangan ambil detail employee; tunggu payroll/accounting journal contract. |
| CRM | Deferred | Belum terkait langsung dengan HR MVP. |
| DMS malware scanning | Deferred | Evaluasi ulang setelah MVP; saat ini tidak memakai ClamAV/scanner berbayar. |
| DMS multi-server/object storage | Deferred | MVP memakai private local disk single server. |
| HR export Excel/PDF besar | Deferred | Jika dibuat, export besar masuk queue. |
| HR Integration outbox/webhook/public API | Deferred | Saat ini contract internal PHP + read-only commands saja. |

## Risiko yang Masih Perlu Dijaga

- Jangan biarkan Attendance/Payroll membaca model internal HR langsung tanpa snapshot/contract.
- Jangan kirim field sensitif ke integration payload: salary, bank, document number, DMS path/URL/token, password, token, confidential notes.
- Jangan membuat storage engine dokumen kedua di HR.
- Jangan menambahkan mutation ke HR Reports.
- Jangan menampilkan atau mengelola role `super-system` untuk actor non-super-system.
- Jangan mencampur formatting massal dengan perubahan behavior.

## Prioritas Lanjut yang Disarankan

Urutan paling aman setelah status MVP ini:

1. Polish dashboard utama agar membaca ringkasan Console, HR, DMS, dan aktivitas sistem. **Status: berjalan.**
2. Telusuri ulang HR foundation modules satu per satu untuk mencari gap kecil sebelum Attendance. Lihat [HR Foundation Review Plan](hr/foundation-review-plan.md).
3. Buat SOP release/dev workflow untuk branch `dev -> main`, migration, seed, quality gate, dan rollback lokal. Lihat [SOP Release dan Dev Workflow](release-dev-workflow.md).
4. Baru mulai Attendance MVP dengan input dari [HR Integration Contracts](hr/integration-contracts/README.md).

Rekomendasi saat ini: mulai dari dashboard utama, karena project sudah punya banyak data MVP tetapi halaman ringkasan utama masih bisa dibuat lebih informatif.
