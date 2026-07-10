# Data Lifecycle dan Soft Delete

Dokumen ini menjadi pegangan untuk menentukan data mana yang boleh dihapus permanen, mana yang harus memakai soft delete, dan mana yang sebaiknya dibuat immutable.

## Prinsip Umum

- Data master, histori bisnis, transaksi, identitas, dokumen, dan data yang direferensikan module lain memakai soft delete.
- Data audit, login activity, queue history, cache, temporary upload, dan snapshot teknis tidak memakai soft delete; gunakan retention cleanup jika perlu.
- Data finansial yang sudah posted sebaiknya tidak dihapus, tetapi dibatalkan memakai void, reversal, atau adjustment.
- Restore data harus menjaga relasi penting seperti role, media, audit, parent-child, dan snapshot integrasi.

## Implementasi Saat Ini

### Root / Console

`users` memakai soft delete karena user menjadi identitas lintas module untuk audit, login activity, created by, updated by, dan ownership data.

Saat user dihapus dari User Management atau profile setting:

- record `users` diberi `deleted_at`;
- avatar/media tidak langsung dihapus agar restore tetap utuh;
- role dan permission pivot tetap dipertahankan;
- audit log tetap mencatat aksi archive, restore, dan force delete;
- restore tersedia melalui permission `users.restore`;
- force delete tersedia melalui permission `users.force-delete` dan hanya untuk user yang sudah masuk arsip.

Module Console yang tidak memakai soft delete:

- Access Control roles/permissions: konfigurasi authorization Spatie yang harus tetap sederhana agar cache, guard, pivot, dan policy tidak rancu.
- Audit Log: histori immutable.
- Login Activity: histori keamanan.
- Queue Monitor: data operasional.
- Scheduler Monitor: data operasional.
- System Setting: lebih cocok update/versioning.
- Notification Template: lebih cocok update/versioning.

### HR

`hr_departements` memakai soft delete karena departement adalah master organisasi dan akan direferensikan oleh employee, attendance, payroll, approval, dan report.

Delete departement tetap ditolak jika masih punya child aktif. Ini menjaga struktur organisasi tidak rusak.

`hr_positions` memakai soft delete karena jabatan menjadi referensi employee profile, approval flow, attendance, payroll, dan report headcount.

`hr_job_levels` memakai soft delete karena level/grade jabatan menjadi referensi employee profile, approval, benefit, payroll, dan report organisasi.

`hr_work_locations` memakai soft delete karena lokasi kerja menjadi referensi employee profile, attendance area, shift, payroll, dan report organisasi.

Module HR berikutnya yang disarankan memakai soft delete:

- employees
- employment statuses
- organization structures
- employee documents
- employee contracts
- employee movements
- onboarding/offboarding records

## Rekomendasi Per Project

### Attendance

Soft delete untuk attendance records, schedules, shifts, correction requests, leave requests, dan overtime requests. Data ini mempengaruhi payroll dan dispute.

### Payroll

Soft delete untuk salary components, payroll profiles, reimbursements, dan draft payroll runs. Payroll run yang sudah approved/posted sebaiknya immutable dan dibatalkan lewat void/reversal.

### Accounting

Soft delete untuk master data seperti chart of accounts draft, vendors, customers, tax profiles, dan attachment. Journal, invoice, payment, dan posting finansial yang sudah final sebaiknya immutable.

### CRM

Soft delete untuk leads, contacts, companies, deals, activities, notes, dan campaign records agar histori pipeline bisa dipulihkan.

### Document Management

Soft delete untuk folders, documents, versions, approvals, dan shares. Document Management sebaiknya punya trash/restore dan versioning.

## Checklist Saat Membuat Model Baru

- Apakah data ini akan direferensikan module lain?
- Apakah data ini punya nilai historis, audit, atau legal?
- Apakah data ini muncul di report atau snapshot?
- Apakah user mungkin perlu restore?
- Apakah delete permanen bisa merusak relasi?

Jika dua atau lebih jawabannya "ya", gunakan soft delete.

## Checklist Implementasi Soft Delete

- Tambahkan `deleted_at` lewat migration baru.
- Tambahkan trait `SoftDeletes` di model.
- Pastikan query default tetap mengecualikan data terhapus.
- Pastikan unique constraint tetap dipikirkan saat data soft-deleted.
- Pastikan delete service mencatat audit log.
- Jangan hapus media/attachment jika restore masih dibutuhkan.
- Update test dari `assertDatabaseMissing` ke `assertSoftDeleted`.
- Dokumentasikan keputusan di module guide atau roadmap terkait.
