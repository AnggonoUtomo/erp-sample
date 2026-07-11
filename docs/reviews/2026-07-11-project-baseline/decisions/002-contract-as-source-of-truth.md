# ADR-002: Manifest dan Schema sebagai Sumber Contract Modul

## Status
Proposed

## Context
Guide menyebut file wajib, sedangkan runtime memakai exports dan memiliki pengecualian aktual.

## Decision
Formalkan schema manifest dan validator read-only; docs dan generator mengikuti schema tersebut.

## Alternatives
Dokumentasi-only ditolak karena drift tidak terdeteksi. Convention-only ditolak karena optional exports ambigu.

## Consequences
Drift terdeteksi dini; schema perlu versioning dan escape hatch eksplisit.

