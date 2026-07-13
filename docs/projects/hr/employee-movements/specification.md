# Specification: Employee Movements

## Objective

Menyimpan histori perubahan work profile employee dan menjadikan movement yang sudah diterapkan sebagai satu-satunya jalur resmi untuk perubahan assignment bertanggal. Slice pertama memungkinkan HR membuat transfer DRAFT, melihat snapshot before/after, lalu menerapkannya secara atomik untuk tanggal hari ini.

## Scope vertical slice 01

- Movement type hanya `TRANSFER`.
- Target yang dapat berubah: `departement_id`, `position_id`, `work_location_id`, dan `supervisor_id`.
- Status movement: `DRAFT` atau `APPLIED`.
- `effective_date` wajib sama dengan business date hari ini.
- Apply mengunci movement dan employee, memastikan snapshot before belum stale, memperbarui Employees, menyimpan actor/time, dan audit dalam satu transaction.
- List paginated menampilkan employee, reason, status, effective date, serta label before/after.

## Non-scope

- Promotion, demotion, job level, employment status/type change.
- Future scheduling, backdate, approval bertingkat, cancellation, archive/restore.
- Perubahan Employee Contract, Payroll, Attendance, notification, dan public integration event.

## Data contract

`hr_employee_movements`: employee, movement type, effective date, status, reason, notes, JSON `before_values`/`after_values`, created/applied actor, applied timestamp, timestamps, dan soft delete. Snapshot JSON hanya berisi ID assignment yang diizinkan; label dirender dari master terkait.

## Route design

```txt
GET  /hr/employee-movements
POST /hr/employee-movements
POST /hr/employee-movements/{employeeMovement}/apply
```

Permissions: `employee-movements.view`, `create`, `apply`, `manage`.

## Acceptance criteria

- HR berizin dapat membuat DRAFT dengan before/after yang benar dan minimal satu perubahan.
- Position target harus berasal dari departemen target/current; supervisor tidak boleh employee sendiri.
- Apply hanya menerima DRAFT efektif hari ini dan memperbarui employee + movement atomik.
- Stale before snapshot, apply berulang, atau user tanpa permission tidak mengubah database.
- Histori menampilkan before/after; audit create dan applied tersedia.

## Commands dan test plan

```bash
php artisan test --filter=HREmployeeMovement
php artisan module:validate
vendor/bin/pint --test
npm run lint:check
npm run format:check
npm run typecheck
npm run test:frontend
npm run build
php artisan test
```

## Boundaries

- Always: FormRequest, policy server-side, transaction + lock saat apply, audit, soft delete.
- Ask first: future/backdate, schema baru, approval, public event.
- Never: hard delete histori, direct mutation status/type/contract, import model Payroll.
