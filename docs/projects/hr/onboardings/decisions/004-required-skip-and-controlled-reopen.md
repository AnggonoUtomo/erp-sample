# ADR-004: Required skip dan controlled reopen

## Status

Accepted — 2026-07-15

## Context

Operasional onboarding membutuhkan pengecualian untuk task yang tidak relevan dan koreksi ketika completion evidence salah. Tanpa batas khusus, required task dapat dilewati terlalu mudah atau evidence terminal dapat diubah tanpa jejak.

## Decision

- Semua skip membutuhkan alasan maksimal 2.000 karakter.
- Optional task dapat di-skip oleh user dengan `onboardings.task-update` atau `onboardings.manage`.
- Required task membutuhkan permission tambahan `onboardings.task-skip-required`; permission diberikan kepada admin dan HR manager, bukan HR officer/viewer.
- Skip hanya berlaku pada task `PENDING` atau `IN_PROGRESS` ketika onboarding `IN_PROGRESS`.
- Reopen hanya berlaku pada task `COMPLETED` atau `SKIPPED`, membutuhkan alasan, dan mengembalikan task ke `PENDING`.
- Reopen membersihkan completion/skip evidence aktif, lalu menyimpan `reopened_by`, timestamp, dan reason. Audit mempertahankan status sebelum/sesudah dan alasan transition.
- Repeated atau invalid transition ditolak tanpa state dan audit tambahan.

## Consequences

- Progress memperlakukan valid `SKIPPED` sebagai terminal dan menurun kembali setelah reopen.
- Task assignment terminal tetap immutable sampai task dibuka kembali.
- Frontend menyembunyikan required-skip bagi user tanpa permission khusus, tetapi server policy/service tetap menjadi security boundary.
- Approval workflow terpisah untuk required skip belum dibuat; permission khusus dan audit menjadi kontrol MVP.

## Alternatives considered

- **Melarang required skip sepenuhnya:** terlalu kaku untuk pengecualian operasional yang sah.
- **Mengizinkan seluruh HR officer:** ditolak karena mengurangi completion invariant.
- **Menghapus histori terminal saat reopen tanpa metadata:** ditolak karena tidak audit-friendly.
