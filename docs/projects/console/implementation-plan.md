# Implementation Plan: Console Project

## Overview

Plan ini menata ulang Console secara konseptual, bukan memerintahkan rebuild. Karena Console sudah ada, setiap fase dimaknai sebagai penelusuran + dokumentasi + koreksi incremental jika ditemukan gap. Urutan mengikuti dependency operasional: identity lebih dulu, baru settings, observability, runtime operations, dan backup.

## Dependency graph

```txt
Console Auth + User identity
        │
        ▼
Access Control + permissions
        │
        ├──► User Management lifecycle
        │
        ├──► System Settings
        │
        ├──► Notification Templates
        │
        └──► Monitor/Backup mutation gates
        │
        ▼
Audit Logs + Login Activities + Activity Center
        │
        ▼
Queue Monitor + Scheduler Monitor
        │
        ▼
Backup Restore + runbook
        │
        ▼
Console UI shell and module guide
```

## Architecture decisions

- Console adalah operational foundation, bukan domain bisnis.
- Route Console tidak memakai prefix project.
- `super-system` adalah role protected dan hanya visible untuk akun super-system.
- Roles/permissions Spatie tidak memakai soft delete.
- Audit Logs bersifat immutable read-only.
- Backup full harus signed dan diverifikasi sebelum restore.
- Queue/scheduler controls harus permission-gated.

Lihat [ADR-001](decisions/001-console-operational-foundation.md).

## Phase 1 — Foundation identity dan authorization

1. Telusuri Console auth/dashboard/layout.
2. Telusuri `AccessControls`.
3. Telusuri `UserManagements`.
4. Formalisasi role `super-system`, protected role, assignment boundary, impersonation boundary.

### Checkpoint A — Identity boundary

- User login, role, permission, dan protected role terdokumentasi.
- Mutation route users/access-control punya denial tests.
- UI tidak memberi jalan assign/update/delete `super-system` dari akun biasa.

## Phase 2 — Runtime settings dan notification

1. Telusuri `SystemSettings`.
2. Telusuri `NotificationTemplates`.
3. Evaluasi secret masking/encryption, email automation, password policy, delete account visibility, map config, maintenance mode.

### Checkpoint B — Config boundary

- Settings yang berbahaya dilindungi permission.
- Secret tidak dikirim plaintext.
- Email/log delivery mode terdokumentasi.
- Template update/preview aman.

## Phase 3 — Observability

1. Telusuri `ActivityCenters`.
2. Telusuri `AuditLogs`.
3. Telusuri `LoginActivities`.
4. Evaluasi field audit/log yang sensitif.

### Checkpoint C — Audit and activity boundary

- Activity center hanya read model.
- Audit log immutable dan tidak menyalin secret.
- Login activity tidak menyimpan password/token.

## Phase 4 — Runtime operations

1. Telusuri `QueueMonitors`.
2. Telusuri `SchedulerMonitors`.
3. Pastikan retry/forget/flush/run due task permission-gated.
4. Catat risiko operasi runtime di production.

### Checkpoint D — Runtime control boundary

- View dan manage permission terpisah.
- Operation destructive tidak bisa dijalankan user tanpa permission.
- Command/run behavior terdokumentasi.

## Phase 5 — Backup restore

1. Telusuri `BackupRestores`.
2. Cocokkan dengan signed full-backup runbook.
3. Evaluasi SQL dump reader/executor, ZIP builder/restorer, settings backup, signature/authenticity.
4. Pastikan restore full memakai verification/dry-run yang jelas.

### Checkpoint E — Recovery boundary

- Backup dapat diverifikasi.
- Restore tidak menerima unsigned/legacy unsafe archive.
- DMS private storage masuk coverage bila ada.
- Runbook sesuai behavior aktual.

## Phase 6 — Console documentation package complete

1. Buat module guide Console.
2. Buat roadmap Console module-by-module.
3. Buat submodule docs jika diperlukan.
4. Tambahkan final quality checkpoint.

### Final checkpoint

- README/spec/plan/tasks/ADR sinkron.
- Gap correction guide tersedia.
- Semua module Console punya status evaluasi.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Rebuild besar tanpa alasan | High | Dokumentasi dulu, koreksi incremental per module |
| Role protected bocor ke user biasa | High | Conditional visibility + backend validation + tests |
| Secret settings bocor ke frontend | High | Masking/encryption + response allowlist |
| Audit menyimpan data sensitif | High | Audit allowlist dan test regression |
| Queue/scheduler dipakai bypass business rule | Medium | Manage permission + audit operation |
| Restore merusak environment aktif | High | Signature verification, dry-run, explicit confirmation |
| Console menjadi tempat domain bisnis | Medium | ADR boundary dan docs lint manual |

## Rollback strategy

- Dokumentasi additive dapat direvisi tanpa mengubah runtime.
- Koreksi code dilakukan task kecil dan bisa direvert per commit.
- Jika rule protected role terlalu ketat, revisi policy/visibility tanpa menghapus role.
- Jika backup/restore behavior berubah, update ADR/runbook sebelum implementasi.

## Approval checkpoint

Setelah dokumen ini selesai, langkah berikutnya adalah user memilih apakah kita:

1. lanjut membuat `docs/projects/console/module-guide.md` dan `roadmap.md`, atau
2. langsung mulai `Task 01 — Console shell, auth, dan dashboard baseline`.

