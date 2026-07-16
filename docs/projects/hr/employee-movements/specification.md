# Specification: Employee Movements

## Objective

Menyimpan histori perubahan work profile employee dan menjadikan movement yang sudah diterapkan sebagai satu-satunya jalur resmi untuk perubahan assignment bertanggal. Slice saat ini memungkinkan HR membuat transfer, promotion, demotion, atau employment change DRAFT, melihat snapshot before/after, lalu menerapkannya secara atomik saat movement sudah due.

## Scope saat ini

- Movement type: `TRANSFER`, `PROMOTION`, `DEMOTION`, dan `EMPLOYMENT_CHANGE`.
- Target transfer yang dapat berubah: `departement_id`, `position_id`, `work_location_id`, dan `supervisor_id`.
- Target promotion/demotion wajib mengubah `job_level_id` ke job level aktif.
- Target employment change dapat mengubah `employment_status_id` atau `employment_type_id` ke master aktif.
- Perubahan `employment_type_id` wajib memiliki active contract dengan employment type yang sama dan efektif pada tanggal movement.
- Status movement: `DRAFT`, `APPROVED`, `APPLIED`, atau `CANCELLED`.
- `effective_date` boleh hari ini atau masa depan; backdate ditolak saat create.
- Approval wajib sebelum apply. Approval mengulang stale/profile guard.
- Manual apply dan scheduler hanya menerapkan movement `APPROVED` yang sudah mencapai effective date.
- Cancellation berlaku untuk `DRAFT` atau `APPROVED`, wajib menyimpan alasan, actor, dan timestamp.
- Archive v1 hanya untuk `CANCELLED`; restore mengembalikan record soft-deleted tanpa hard delete.
- Apply menerbitkan event integration `EmployeeMovementAppliedV1`.
- Apply mengunci movement dan employee, memastikan snapshot before belum stale, memperbarui Employees, menyimpan actor/time, dan audit dalam satu transaction.
- List paginated menampilkan employee, reason, status, effective date, serta label before/after.

## Non-scope

- Backdate, approval bertingkat, archive/restore.
- Mutasi Employee Contract dari movement, Payroll, Attendance, notification, dan public integration event.

## Data contract

`hr_employee_movements`: employee, movement type, effective date, status, reason, notes, JSON `before_values`/`after_values`, created/approved/applied/cancelled/archived actor, approved/applied/cancelled/archived timestamp, cancel reason, timestamps, dan soft delete. Snapshot JSON hanya berisi ID work profile yang diizinkan (`departement_id`, `position_id`, `job_level_id`, `employment_status_id`, `employment_type_id`, `work_location_id`, `supervisor_id`); label dirender dari master terkait.

## Route design

```txt
GET  /hr/employee-movements
POST /hr/employee-movements
POST /hr/employee-movements/{employeeMovement}/approve
POST /hr/employee-movements/{employeeMovement}/apply
POST /hr/employee-movements/{employeeMovement}/cancel
DELETE /hr/employee-movements/{employeeMovement}
PATCH /hr/employee-movements/{employeeMovement}/restore
```

Permissions: `employee-movements.view`, `create`, `approve`, `apply`, `cancel`, `archive`, `restore`, `manage`.

## Acceptance criteria

- HR berizin dapat membuat DRAFT dengan before/after yang benar dan minimal satu perubahan.
- Position target harus berasal dari departemen target/current; supervisor tidak boleh employee sendiri.
- Promotion/demotion wajib memilih job level aktif yang berbeda dari current profile.
- Transfer tidak boleh mengubah job level.
- Employment change wajib mengubah status atau type employment aktif.
- Employment type change wajib didukung active contract yang efektif.
- Approve hanya menerima DRAFT dan tidak mengubah profile employee.
- Apply hanya menerima APPROVED yang sudah due dan memperbarui employee + movement atomik.
- Cancel hanya menerima DRAFT/APPROVED dan tidak mengubah profile employee.
- Archive/restore memakai soft delete dan tidak mengubah profile employee.
- Event `EmployeeMovementAppliedV1` hanya memuat ID, movement type, effective date, changed fields, dan actor ID.
- Stale before snapshot, apply berulang, atau user tanpa permission tidak mengubah database.
- Histori menampilkan before/after; audit create dan applied tersedia.

## Commands

```bash
php artisan hr:employee-movements:apply-due --date=2026-07-17
php artisan hr:employee-movements:apply-due --date=2026-07-17 --dry-run
```

Command memakai tanggal eksplisit `YYYY-MM-DD`, memilih APPROVED dengan `effective_date <= date`, dan mengulang guard apply yang sama dengan manual action. `--dry-run` hanya menghitung due movement tanpa mutation.

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
- Ask first: backdate, schema baru setelah cancellation v1, approval, public event.
- Never: hard delete histori, mutasi Employee Contract dari movement, import model Payroll.
