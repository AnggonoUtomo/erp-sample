# ADR-001: Hierarchy node is separate from department

## Status

Proposed — 2026-07-14.

## Context

Organization Structures adalah hierarki formal parent-child untuk organization chart, reporting line, dan fondasi approval. Risiko utama adalah cycle/orphan hierarchy; parent dihapus; struktur berubah tanpa effective date. Keputusan boundary harus stabil sebelum module menjadi sumber data Attendance, Payroll, atau consumer lain.

## Decision

Departement adalah master unit, sedangkan node menyatakan penempatan dan hierarki; pemisahan mencegah struktur organisasi terkunci pada satu bentuk master.

Master tetap dimiliki `HR/OrganizationStructures`. Consumer memakai reference/service/contract versioned, tidak mengubah tabel internal. Mutation wajib authorization, transaction bila menyentuh relasi, audit aman, dan referential guard.

## Alternatives considered

### Menyalin master ke setiap consumer

Ditolak karena menghasilkan drift dan arti code yang berbeda.

### Mengakses model/table secara langsung lintas project

Ditolak karena coupling schema dan lifecycle terlalu kuat.

### Menaruh seluruh aturan consumer pada master

Ditolak karena employee supervisor history, matrix organization multi-parent, approval rule engine, effective-dated reorganization tetap milik domain terkait.

## Consequences

- Code/reference harus diperlakukan sebagai contract bisnis.
- Perubahan yang berdampak retroaktif membutuhkan migration/approval eksplisit.
- Consumer bertanggung jawab atas behavior domainnya sendiri.
- ADR baru diperlukan bila ownership, effective dating, atau topology integrasi berubah.

## Approval questions

- Apakah keputusan identity/ownership ini diterima?
- Apakah force-delete harus dilarang setelah reference pertama?
- Consumer pertama mana yang membutuhkan read contract v1?
