# Console Project

Dokumen ini mendefinisikan project `Console` sebagai base module operasional aplikasi. Dokumen dibuat setelah MVP HR berjalan, tetapi sengaja ditulis dari sudut pandang “Console belum dibuat” agar programmer dan agent berikutnya bisa menelusuri alasan, urutan kerja, batasan, dan quality gate Console secara rapi seperti project HR.

Console bukan modul bisnis seperti HR, CRM, atau Payroll. Console adalah fondasi administrasi, identity, authorization, konfigurasi sistem, observability, notification, queue/scheduler monitor, dan backup/restore. Project bisnis boleh memakai layanan Console, tetapi tidak boleh mencampur domain bisnis ke dalam Console.

## Status

`Documentation baseline created — 2026-07-19`.

Dokumen ini belum berarti seluruh Console perlu dibangun ulang. Tujuannya adalah membuat peta kerja dan audit trail yang sebelumnya terlewat, lalu nanti kita telusuri module Console satu per satu. Jika saat penelusuran ditemukan gap, koreksi dibuat sebagai task incremental, bukan rewrite besar.

## Urutan baca

1. [Specification](specification.md) — requirement, non-scope, struktur folder, boundary backend/frontend, command design, acceptance criteria, dan test plan.
2. [ADR-001: Console sebagai operational foundation](decisions/001-console-operational-foundation.md) — alasan Console menjadi project dasar, bukan domain bisnis.
3. [Implementation plan](implementation-plan.md) — urutan vertical slice dari identity sampai backup/restore.
4. [Tasks](tasks.md) — daftar task kecil untuk menelusuri, memformalkan, dan mengoreksi Console module-by-module.
5. [01 — Console Shell Baseline](01-console-shell-baseline.md) — hasil telusur login, dashboard, layout, sidebar, header, shared props, dan theme.
6. [02 — Access Control Boundary](02-access-control-boundary.md) — hasil telusur role/permission CRUD, protected `super-system`, permission module panel, policy, dan denial matrix.

## Relasi lintas dokumen

- [Project module guide](../../guides/project-module-guide.md) — aturan umum project/module, route Console tanpa prefix project, dan module contract.
- [Starterkit blueprint](../../architecture/starterkit-blueprint.md) — blueprint awal Console, UI, identity, settings, audit, queue, scheduler, dan backup.
- [Data lifecycle](../../architecture/data-lifecycle.md) — lifecycle root/Console, soft delete users, dan module yang tidak memakai soft delete.
- [Baseline review](../../reviews/2026-07-11-project-baseline/README.md) — review repository-level yang sempat menemukan beberapa gap Console.
- [Mutation authorization matrix](../../reviews/2026-07-11-project-baseline/08-mutation-authorization-matrix.md) — daftar route mutasi global dan permission denial matrix.
- [Backup signature runbook](../../reviews/2026-07-11-project-baseline/09-backup-signature-runbook.md) — runbook full-backup signed.
- [HR module guide](../hr/module-guide.md) — HR memakai Console sebagai pola UI/permission/audit dan optional Console User boundary.

## Module Console yang ditelusuri

| Module | Fungsi utama | Bentuk MVP |
|---|---|---|
| `AccessControls` | Role, permission, dan permission module panel | CRUD role/permission terbatas; `super-system` protected |
| `UserManagements` | Akun login, avatar, activation/password link, impersonation | User lifecycle, soft delete/restore, role/direct permission |
| `SystemSettings` | Konfigurasi sistem runtime | Branding, localization, email, security, password policy, map, maintenance, pagination |
| `NotificationTemplates` | Template notifikasi/email | Update dan preview template tanpa membocorkan secret |
| `ActivityCenters` | Ringkasan aktivitas user | Read-only notification/activity center |
| `AuditLogs` | Audit trail immutable | Read-only log mutasi penting |
| `LoginActivities` | Histori login sukses/gagal | Read-only security observability |
| `QueueMonitors` | Monitor failed jobs | Read-only view + controlled retry/forget/flush |
| `SchedulerMonitors` | Monitor scheduled task | Read-only view + controlled run due task |
| `BackupRestores` | Backup/restore database, storage, dan settings | Signed backup, verification, dry-run/restore, restore hardening |
| Console Dashboard/Auth | Entry point administrasi | Login, dashboard, layout/sidebar/header |

## Prinsip Console

- Console adalah operational foundation, bukan tempat proses bisnis HR/Payroll/CRM.
- Identity dan authorization harus aman sebelum project bisnis memakai data penting.
- Mutation Console wajib lewat policy/permission dan audit.
- Backup/restore harus bisa membuktikan integritas dan authenticity.
- Queue/scheduler monitor tidak boleh menjadi jalan pintas untuk bypass authorization.
- Settings tidak boleh mengekspos secret plaintext ke frontend.
- UI Console menjadi canonical pattern untuk project lain, tetapi project lain tetap punya boundary sendiri.

## Bentuk final MVP yang diharapkan

MVP Console dianggap siap jika:

- akun `super-system` tersedia dan role-nya protected;
- user/role/permission bisa dikelola dengan guard yang jelas;
- system settings memengaruhi runtime tanpa membocorkan secret;
- audit, login activity, activity center, notification template, queue monitor, scheduler monitor, dan backup/restore tersedia;
- semua route mutasi memiliki permission denial test;
- dokumentasi module-by-module bisa dieksekusi sebagai backlog koreksi incremental.
