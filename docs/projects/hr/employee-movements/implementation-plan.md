# Implementation Plan: Employee Movements

## Architecture

FormRequest → DTO → Service → Transaction → EmployeeMovement/Employee. Snapshot before dibuat server saat DRAFT, bukan dipercaya dari browser. Apply memakai optimistic snapshot check dan row lock.

## Urutan

1. Contract/docs dan migration/model/manifest.
2. Feature test RED untuk create/list/apply/denial/stale state.
3. Backend create/list dan audit.
4. Backend apply atomik dan audit.
5. Frontend form, table histori, before/after, apply action.
6. Full quality gate dan scope review.

## Risiko

| Risiko | Mitigasi |
|---|---|
| Profile berubah setelah draft | Bandingkan current assignment dengan before snapshot saat apply |
| Position/departemen tidak cocok | Validasi relasi pada request dan ulangi pada service |
| Partial update | Satu database transaction dengan row lock |
| Movement dipakai untuk promotion/status | Allowlist field transfer; task terpisah untuk type lain |

## Rollback

Migration memiliki `down()`. Commit slice dapat direvert; setelah data production ada, rollback memakai forward migration dan tidak menjatuhkan histori.
