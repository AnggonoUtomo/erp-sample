# Tasks: Employee Movements

## Task 01 — Transfer hari ini vertical slice

**Tujuan:** create/list DRAFT dan apply transfer atomik ke Employees.

**Files:** module contract, migration/model, request/DTO/service/transaction/controller/policy/provider, page typed, feature test.

**Acceptance criteria:**

- [x] DRAFT menyimpan server-generated before/after dan minimal satu perubahan.
- [x] Histori before/after tersedia sesuai permission.
- [x] Apply hari ini atomic, audited, stale-safe, dan idempotency denial teruji.

**Hasil implementasi:** selesai 2026-07-13. Test fokus lulus dengan 4 skenario; quality gates dicatat pada README setelah verifikasi final.

**Test:** `php artisan test --filter=HREmployeeMovement`

## Next tasks

2. Promotion/demotion + Job Level.
3. Employment Status/Type change dan contract coordination.
4. Future-effective scheduler dan cancellation.
5. Archive/restore, approval, dan integration snapshot/event.
