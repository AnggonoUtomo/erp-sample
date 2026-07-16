# Checkpoint B — Offboarding Draft Snapshot

**Status:** PASS

**Tanggal:** 2026-07-16

**Verified baseline:** `f97bc81`

## Scope yang diverifikasi

Checkpoint ini menutup Task 04–06:

- draft Offboarding dan ordered task snapshot;
- validasi employee, optional contract, template, target final status, owner, serta exit context;
- active identity dan request fingerprint;
- identical retry, conflict rejection, dan database concurrency guard;
- list dan detail read model;
- progress deterministic menggunakan business date eksplisit;
- archived detail, authorization, audit, dan frontend read-only.

Checkpoint ini belum mencakup activation, task mutation, readiness, cancellation, employment finalization, archive/restore operasional, atau integration event.

## Evidence quality gate

| Gate | Hasil |
| --- | --- |
| `php artisan module:validate` | PASS |
| `php artisan test --filter=Offboarding` | PASS — 23 tests, 255 assertions |
| `php artisan test` | PASS — 394 tests, 2.122 assertions |
| `vendor/bin/pint --test` | PASS |
| `npm run lint:check` | PASS |
| `npm run format:check` | PASS |
| `npm run typecheck` | PASS |
| `npm run test:frontend` | PASS — 7 files, 13 tests |
| `npm run build` | PASS — 2.188 modules transformed |
| `git diff --check` | PASS |

## End-to-end review

- Authorized HR dapat membuat draft dari halaman list.
- Case dan seluruh task snapshot dibuat dalam transaction yang sama.
- List dipaginate dan menyediakan link detail dengan business date eksplisit.
- Detail membaca ordered task snapshot, bukan current template items.
- Active maupun archived detail memiliki empty state dan progress yang konsisten.
- Employee dan Employee Contract tidak dimutasi pada draft/read flow.

## Atomicity, snapshot, dan audit

- Audit failure yang diinjeksi me-rollback Offboarding dan seluruh task.
- Edit/archive template setelah create tidak mengubah title, category, required flag, assignment context, order, due offset/date, atau status task existing.
- Audit create hanya menyimpan metadata operasional minimum; exit reason, notes, fingerprint, dan identity tidak disalin ke audit values.
- Identical retry mengembalikan aggregate existing tanpa task atau audit baru.

## Identity dan concurrency

- Active identity memakai `employee:{id}` sesuai [ADR-003](decisions/003-active-identity-and-idempotency.md).
- Fingerprint SHA-256 berasal dari payload bisnis yang sudah dinormalisasi.
- Request berbeda untuk employee dengan case aktif ditolak.
- Unique nullable constraint pada `active_identity_key` mencegah competing insert.
- Identity dan fingerprint disembunyikan dari model serialization dan Inertia props.

## Authorization dan security review

- Route list, create, dan detail memakai authentication serta policy middleware.
- Guest dan authenticated user tanpa permission ditolak.
- Archived history hanya dapat dibaca melalui route detail yang tetap policy-backed.
- `business_date` divalidasi dengan format `Y-m-d`.
- Props memakai field allowlist dan tidak membawa email, personal data Employee, active identity, request fingerprint, atau source template item id.
- React merender reason/notes sebagai text sehingga tidak membuka raw HTML/XSS surface.
- Tidak ada route mutation Employee/Contract, public event, atau direct Attendance/Payroll integration.

## Correctness dan performance review

- Progress membedakan terminal, completed, skipped, required incomplete, optional incomplete, overdue, dan percentage.
- Overdue hanya berlaku pada task non-terminal dengan due date sebelum business date.
- Task selalu mengikuti `sort_order`.
- Detail eager-load seluruh relation yang dipakai dan tidak memiliki N+1 loop.
- List tetap paginated 20 item.
- Frontend composer dipisah dari presenter, summary cards, dan task list.

## Findings

Tidak ada blocker untuk Task 07.

Residual constraints yang memang belum masuk scope:

- active identity belum dilepas karena cancel/completion belum tersedia;
- task belum memiliki assignee atau completion/skip evidence;
- `READY_FOR_EXIT` dan effective finalization belum diimplementasikan;
- archive/restore operasional case belum dibuka.

## Gate decision

Checkpoint B **PASS**. Task 07 — Activate offboarding boleh dimulai dengan syarat:

- hanya aggregate `DRAFT`, tidak archived, dan masih memiliki active identity yang valid yang dapat diaktifkan;
- revalidation Employee, optional Contract, target final status, dan duplicate identity dilakukan dengan row lock;
- transition `DRAFT -> IN_PROGRESS` dan audit berada dalam transaction yang sama;
- retry activation bersifat idempotent dan tidak membuat audit ganda;
- activation tidak mengubah Employee, Employee Contract, task snapshot, exit context, atau integration event.
