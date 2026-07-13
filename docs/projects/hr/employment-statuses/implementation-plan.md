# Implementation Plan: Employment Statuses

## Prinsip

Mulai dari audit baseline, lalu hardening behavior yang sudah ada sebelum menambah scope baru. Setiap task adalah vertical slice kecil dan tidak boleh mengubah lebih dari sekitar lima file tanpa dipecah.

## Dependency graph

```txt
Module contract + schema audit
        ↓
Validation + authorization
        ↓
Lifecycle + referential safety
        ↓
Read contract untuk consumer
        ↓
Frontend/accessibility + documentation
```

Menjadi upstream Employees, Employee Movements, Attendance, dan Payroll.

## Phase 1 — Baseline contract

1. Cocokkan manifest, permission, routes, migration, model, dan UI.
2. Kunci validation/invariant dengan feature tests.
3. Lengkapi global mutation denial inventory.

### Checkpoint A

- Module validator, targeted tests, dan authorization matrix hijau.
- Tidak ada perubahan schema terselubung.

## Phase 2 — Lifecycle safety

4. Audit delete/restore/force-delete terhadap seluruh foreign key consumer.
5. Tambahkan guard dan audit tanpa mengubah ownership.
6. Definisikan stable read contract hanya bila consumer lintas project memerlukannya.

### Checkpoint B

- Delete/restore tidak menghasilkan orphan.
- Contract additive/versioned dan tidak mengekspor model/tabel internal.

## Phase 3 — UX dan readiness

7. Selaraskan filter, empty/error state, permission-aware action, dan aksesibilitas.
8. Perbarui guide lifecycle dan bukti quality gates.

### Final checkpoint

- Acceptance criteria specification hijau.
- Pint, ESLint, Prettier, TypeScript, frontend test/build, backend test, audit dependency, dan diff check lulus.

## Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| perubahan flag berdampak lintas domain; final status diaktifkan tanpa transition policy | Restrict/guard mutation, transaction, audit, dan regression tests |
| Contract terlalu cepat dibuka | Mulai internal; export DTO v1 hanya untuk consumer nyata |
| Scope master bercampur domain consumer | Pertahankan ownership; consumer menyimpan reference, bukan copy behavior |
| Dokumentasi berbeda dari kode | Validasi route/schema/test saat setiap task ditutup |

## Rollback

Setiap task harus dapat direvert per commit. Migration additive memakai rollback teruji; perubahan contract memakai versi baru dan tidak mengubah arti field v1.
