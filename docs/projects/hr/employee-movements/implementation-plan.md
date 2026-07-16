# Implementation Plan: Employee Movements

## Architecture

FormRequest → DTO → Service → Transaction → EmployeeMovement/Employee. Snapshot before dibuat server saat DRAFT, bukan dipercaya dari browser. Apply memakai optimistic snapshot check dan row lock.

## Urutan

1. Contract/docs dan migration/model/manifest.
2. Feature test RED untuk create/list/apply/denial/stale state.
3. Backend create/list dan audit.
4. Backend apply atomik dan audit.
5. Frontend form, table histori, before/after, apply action.
6. Promotion/demotion + job level guard.
7. Employment status/type change + active contract coordination guard.
8. Future-effective create, cancellation, dan due scheduler command.
9. Full quality gate dan scope review.

## Risiko

| Risiko | Mitigasi |
|---|---|
| Profile berubah setelah draft | Bandingkan current assignment dengan before snapshot saat apply |
| Position/departemen tidak cocok | Validasi relasi pada request dan ulangi pada service |
| Partial update | Satu database transaction dengan row lock |
| Movement type mengubah field yang salah | Guard per type: transfer assignment-only, promotion/demotion job-level-only, employment change employment-only |
| Employment type berubah tanpa dasar kontrak | Wajib ada active contract yang efektif pada tanggal movement dan memiliki employment type tujuan |
| Movement memutasi kontrak | Movement hanya membaca contract sebagai guard; lifecycle contract tetap milik EmployeeContracts |
| Scheduler melewatkan tanggal efektif | Command memilih `effective_date <= business date` dan tetap menjalankan stale/profile guard saat apply |
| Cancelled movement ter-apply | Apply hanya menerima status `DRAFT`; cancellation mengubah status menjadi `CANCELLED` dalam transaction |

## Rollback

Migration memiliki `down()`. Commit slice dapat direvert; setelah data production ada, rollback memakai forward migration dan tidak menjatuhkan histori.
