# Roadmap Project Console

Roadmap ini adalah peta status dan arah pengembangan project `Console`. Console adalah fondasi operasional aplikasi: login, user, role, permission, settings, notification, audit, monitoring runtime, dan backup/restore.

Panduan teknis module Console tersedia di [module-guide.md](module-guide.md).

## Status implementasi

| Area | Module/source | Status |
|---|---|---|
| Console Shell | dashboard, login, layout/sidebar/header | tersedia dan terdokumentasi |
| Access Control | `AccessControls` | tersedia; `super-system` protected |
| User Management | `UserManagements` | tersedia; user lifecycle, avatar, activation/reset link, impersonation |
| System Settings | `SystemSettings` | tersedia; secret map/maintenance/email masked/encrypted |
| Notification Templates | `NotificationTemplates` | tersedia; update permission-gated |
| Activity Center | `ActivityCenters` | tersedia; read model dropdown |
| Audit Logs | `AuditLogs` | tersedia; immutable read-only dari user |
| Login Activities | `LoginActivities` | tersedia; login/logout observability |
| Queue Monitor | `QueueMonitors` | tersedia; view/manage split dan output redaction |
| Scheduler Monitor | `SchedulerMonitors` | tersedia; view/manage split, heartbeat, output redaction |
| Backup Restore | `BackupRestores` | tersedia; signed full ZIP v3, private DMS, dry-run |
| Module guide | `module-guide.md` | tersedia |
| Roadmap | dokumen ini | tersedia |

## Phase 1 — Identity dan access boundary

Status: selesai.

Scope:

- Console login/dashboard/layout.
- Access Control role/permission.
- User Management lifecycle.
- Protected role `super-system`.
- Impersonation boundary.

Dokumen:

- [01 — Console Shell Baseline](01-console-shell-baseline.md)
- [02 — Access Control Boundary](02-access-control-boundary.md)
- [03 — User Management Lifecycle](03-user-management-lifecycle.md)
- [Checkpoint A — Identity and Access Boundary](checkpoint-a-identity-access-boundary.md)

Backlog:

- Samakan label Bahasa Indonesia di beberapa page/breadcrumb.
- Hubungkan `lastLogin` User Management ke Login Activities.
- Tambahkan hint eksplisit saat user mencoba membuat/assign `super-system`.
- Samakan MIME allowlist avatar User Management dengan profile settings.

## Phase 2 — Configuration dan notification boundary

Status: selesai.

Scope:

- System Settings.
- Secret masking/encryption.
- Email/log delivery mode.
- Notification Templates lifecycle.

Dokumen:

- [04 — System Settings Boundary](04-system-settings-boundary.md)
- [05 — Notification Templates Lifecycle](05-notification-templates-lifecycle.md)
- [Checkpoint B — Configuration and Notification Boundary](checkpoint-b-configuration-notification-boundary.md)

Backlog:

- Depresiasi/guard legacy credential password notification.
- Safe preview endpoint untuk notification template jika dibutuhkan.
- Audit body policy untuk template.
- Enforcement tambahan security policy seperti single session/email verification bila disetujui.

## Phase 3 — Observability boundary

Status: selesai.

Scope:

- Activity Center.
- Audit Logs.
- Login Activities.
- Secret redaction.
- Read-only observability route.

Dokumen:

- [06 — Activity Center Read Model](06-activity-center-read-model.md)
- [07 — Audit Logs Immutable Boundary](07-audit-logs-immutable-boundary.md)
- [08 — Login Activities Security Observability](08-login-activities-security-observability.md)
- [Checkpoint C — Observability Boundary](checkpoint-c-observability-boundary.md)

Backlog:

- Retention/archive policy audit logs dan login activities.
- PII minimization untuk email/IP/user-agent.
- Audit description safety.
- Tamper-evident audit chain jika ada kebutuhan compliance.
- Security signal summary/alert.

## Phase 4 — Runtime operation boundary

Status: selesai.

Scope:

- Queue Monitor.
- Scheduler Monitor.
- Runtime action permission-gated.
- Output redaction.
- Scheduler heartbeat.

Dokumen:

- [09 — Queue Monitor Runtime Control](09-queue-monitor-runtime-control.md)
- [10 — Scheduler Monitor Runtime Control](10-scheduler-monitor-runtime-control.md)
- [Checkpoint D — Runtime Operation Boundary](checkpoint-d-runtime-operation-boundary.md)

Backlog:

- Audit runtime actions: queue retry/forget/flush dan scheduler run due.
- Operator safety dialog yang lebih informatif.
- Queue driver compatibility warning.
- Scheduler heartbeat troubleshooting UI.
- Domain idempotency contract untuk job/command penting.

## Phase 5 — Recovery boundary

Status: selesai.

Scope:

- Settings backup JSON.
- Full signed ZIP v3.
- HMAC signature lintas environment.
- Private DMS backup/restore.
- Unsafe/legacy restore rejection.
- Dry-run full restore.

Dokumen:

- [11 — Backup Restore Signed Recovery Boundary](11-backup-restore-signed-recovery-boundary.md)
- [Checkpoint E — Recovery Boundary](checkpoint-e-recovery-boundary.md)
- [Backup signature runbook](../../reviews/2026-07-11-project-baseline/09-backup-signature-runbook.md)

Backlog:

- Restore drill SOP di UI.
- Multi-key verification untuk rotasi.
- Asymmetric signature evaluation.
- Restore staging directory.
- Backup size/runtime observability.
- Production restore drill.

## Phase 6 — Console documentation package

Status: tersedia.

Scope:

- Console module guide.
- Console roadmap.
- README/tasks cross-link.
- Final checkpoint berikutnya.

Output:

- [Panduan Module Project Console](module-guide.md)
- Dokumen roadmap ini.

Backlog:

- Putuskan apakah Console perlu folder subproject per module seperti HR.
- Buat guide-plan koreksi gabungan setelah final checkpoint.
- Tambahkan SOP human-user untuk Console jika pengujian manual mulai dilakukan.

## Urutan operasional yang disarankan untuk user/admin

Untuk instalasi atau pengujian manual dari nol:

1. Login sebagai akun super-system.
2. Cek Access Control dan pastikan role `super-system`, `admin`, `staff`, dan role project lain muncul sesuai actor.
3. Buat atau review user admin/staff.
4. Konfigurasi System Settings: branding, localization, email/log, security policy, map, maintenance.
5. Review Notification Templates.
6. Pastikan Activity Center, Audit Logs, dan Login Activities berjalan.
7. Cek Queue Monitor dan Scheduler Monitor.
8. Buat full backup signed ZIP.
9. Lakukan dry-run restore di environment aman.
10. Baru masukkan atau verifikasi data project bisnis seperti HR.

## Definition of done module Console

Sebuah module Console dianggap siap jika:

- route, permission, navigation/provider sesuai kebutuhan;
- policy/FormRequest menutup authorization;
- deny access test tersedia;
- mutation penting diaudit;
- secret tidak masuk response/log/audit;
- frontend punya disabled/loading/error state;
- documentation source-of-truth diperbarui;
- gate module hijau.

## Risiko besar yang tetap dijaga

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Console menampung domain bisnis | boundary kabur | pindahkan ke project pemilik domain |
| Role protected bocor | privilege escalation | `super-system` hidden/protected + tests |
| Secret setting bocor | credential leak | encrypted/masked + audit sanitizer |
| Audit menyimpan PII/secret berlebih | compliance risk | redaction + retention roadmap |
| Queue/scheduler action mengulang side effect | data inconsistency | manage permission + job idempotency |
| Restore gagal parsial | recovery palsu | signed backup + dry-run + restore drill |
| Backup key hilang/bocor | backup tidak valid/forged | secret manager + rotation plan |

## Hubungan dengan project lain

```txt
Console
  -> HR
  -> Document Management
  -> Attendance
  -> Payroll
  -> Accounting
  -> CRM
```

Rule penting:

- Project lain boleh memakai identity/user dari Console.
- Project lain boleh memakai System Settings jika memang setting global.
- Project lain boleh menulis audit lewat service Console dengan payload aman.
- Project lain tidak boleh menyimpan data domainnya di Console.
- Project lain tidak boleh menganggap sidebar/permission frontend sebagai security boundary.
- Backup Restore Console mencakup storage global yang disetujui, tetapi ownership binary/domain tetap pada project pemilik.
