# Snapshot Requirement Pra-SEOS

## Scope

Requirement historis tersimpan dalam specification, implementation plan, task, test evidence, checkpoint, dan consumer handoff pada commit sumber.

## Cara Rekonsiliasi

- Perilaku yang terbukti pada kode dibawa ke baseline SEOS aktif.
- Requirement yang hanya direncanakan tidak dinaikkan menjadi active tanpa owner dan acceptance.
- Angka test lama adalah evidence pada commit/waktu tersebut, bukan klaim untuk working tree saat ini.
- Keputusan accepted lama tidak dihapus; bila bertentangan, ADR aktif mencatat supersession.

## Keterbatasan

Snapshot ini tidak mengulang seluruh requirement agar tidak menciptakan sumber kebenaran paralel. Commit sumber adalah artefak immutable; traceability aktif berada pada `docs/02-requirements/TRACEABILITY-MATRIX.md`.
