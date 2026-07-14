# Checkpoint C — Lifecycle complete

**Status:** PASS  
**Tanggal:** 2026-07-15

## Tujuan pemeriksaan

Memastikan lifecycle onboarding dari draft sampai terminal sudah fail-closed sebelum pekerjaan bergeser ke query operasional, archive/restore, dan overdue command.

## Semantics yang disetujui

- Progress terminal menghitung task `COMPLETED` dan `SKIPPED`.
- Completion onboarding hanya mensyaratkan seluruh task **wajib** terminal secara sah.
- Task opsional yang belum selesai tetap dipertahankan apa adanya dan tidak di-skip otomatis.
- Cancellation tersedia dari `DRAFT` dan `IN_PROGRESS` dengan reason wajib.
- `COMPLETED` dan `CANCELLED` immutable pada MVP; koreksi dilakukan melalui onboarding baru, bukan reopen terminal.
- Permission `onboardings.complete`/`onboardings.cancel` adalah approval operasional MVP tanpa approval dua tahap.

Rationale lengkap tersedia pada [ADR-004](decisions/004-required-skip-and-controlled-reopen.md) dan [ADR-005](decisions/005-terminal-lifecycle-evidence.md).

## Evidence matrix

| Area                | Evidence                                                                          | Hasil                                                                                                           |
| ------------------- | --------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| State contract      | `OnboardingFoundationTest`                                                        | Transisi valid diterima dan transisi lain fail-closed                                                           |
| Activation          | `OnboardingActivationTest`                                                        | Draft valid, conflict, retry, terminal, dan denial tercakup                                                     |
| Task lifecycle      | `OnboardingTaskCompletionTest`, `OnboardingTaskLifecycleTest`                     | Assignment/start/complete/skip/reopen, evidence, serta invalid transition tercakup                              |
| Terminal lifecycle  | `OnboardingLifecycleTest`                                                         | Required incomplete memblokir complete; cancel reason; terminal immutable; audit tercakup                       |
| Route authorization | `OnboardingAuthorizationMatrixTest`                                               | Seluruh 12 mutation diinventarisasi dan wajib memiliki auth + policy middleware                                 |
| Concurrency guard   | Lifecycle/task services dan database unique guard                                 | Lock order konsisten: onboarding lebih dahulu, lalu task/task set; active identity dilindungi unique constraint |
| Frontend semantics  | `onboarding-activation`, `onboarding-task-controls`, `onboarding-lifecycle` tests | Aksi hanya ditampilkan sesuai state/progress/permission; backend tetap security boundary                        |

## Review keamanan dan kualitas

- Reason dan note dibatasi maksimum 2.000 karakter serta dirender dengan escaping React.
- Audit tidak menyimpan token/password; actor, transition, identifier, dan reason bisnis saja.
- Detail eager-load actor sehingga tidak menambah N+1.
- Terminal transition dan audit berada dalam transaction yang sama.
- Tidak ada endpoint generic status update atau hard delete.

## Kesimpulan

Checkpoint C diterima. Semantics progress/completion di atas menjadi baseline untuk Task 11. Perubahan semantics harus melalui ADR baru dan regression test.
