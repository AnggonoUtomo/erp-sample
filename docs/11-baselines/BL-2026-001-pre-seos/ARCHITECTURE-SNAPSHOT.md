# Snapshot Arsitektur Pra-SEOS

## Boundary

Dokumen historis menggambarkan satu Laravel modular monolith dengan area Console, HR, dan Document Management. Struktur folder masih flat/module-local dan keputusan dikumpulkan per project/module.

## Prinsip yang Tetap Dipertahankan

- satu owner untuk data dan behavior;
- contract versioned untuk consumer lintas modul;
- event producer dimiliki source module;
- effective-dated lifecycle untuk contract/movement/offboarding;
- DMS storage privat dan secure delivery;
- privacy/minimal PII pada snapshot HR;
- reporting bersifat read-only.

## Prinsip yang Diganti oleh Baseline Aktif

- Struktur target ditetapkan oleh ADR-0001.
- Shell `HR/IntegrationContracts` dievaluasi ulang oleh ADR-0002/DEP-HR-001.
- Status API, schema, modul, dan test harus mengikuti bukti kode 2026-08-13, bukan klaim planning lama.

## Referensi

Semua path berada di commit `aab3c87a88ccdda64c95051ec72b431648e0ecdf` di bawah `docs/projects/`, `docs/planning/`, dan `docs/reviews/`.
