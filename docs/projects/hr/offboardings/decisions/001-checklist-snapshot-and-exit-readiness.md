# ADR-001: Checklist Snapshot dan Exit Readiness Terpisah dari Completion

## Status

Accepted

## Date

2026-07-16

## Context

Template offboarding berubah dari waktu ke waktu, sedangkan histori setiap employee harus stabil. Selain itu, checklist dapat selesai sebelum exit date. Bila status “completed” dipakai langsung saat checklist selesai, user dapat mengira employment sudah dihentikan padahal Employee dan Contract belum berubah.

## Decision

- Item template disalin menjadi relational task snapshot saat draft dibuat.
- Task menyimpan source identifier, title, description, category, required flag, sort order, due offset/date, dan default assignment context.
- Offboarding memiliki state `READY_FOR_EXIT` setelah seluruh task wajib terminal secara sah.
- `READY_FOR_EXIT` tidak mengubah Employee atau Contract.
- `COMPLETED` hanya dicapai oleh effective finalization pada/ setelah exit date.
- Reopen task pada case ready mengembalikan aggregate ke `IN_PROGRESS`.

## Alternatives considered

### Membaca template live

Ditolak karena edit template mengubah histori dan evidence.

### Checklist completion langsung menjadi completed

Ditolak karena mencampur process readiness dengan employment termination.

### Menyimpan checklist sebagai JSON

Ditolak karena assignment, overdue query, constraint, dan audit per task menjadi sulit.

### Workflow engine generik

Ditolak sebagai abstraksi prematur.

## Consequences

- Histori stabil dan dapat direproduksi.
- User melihat perbedaan readiness dan final exit.
- Data snapshot terduplikasi secara sengaja.
- Create dan ready/reopen memerlukan transaction.
- UI harus menjelaskan bahwa ready belum berarti employee terminated.

## Implementation constraints

- Snapshot dibuat dalam transaction yang sama dengan draft.
- Template archive tidak menghapus snapshot.
- Completion invariant hanya membaca task snapshot.
- Tidak ada binary evidence di module.
- Transition invalid/repeated fail-closed.
