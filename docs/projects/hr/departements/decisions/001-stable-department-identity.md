# ADR-001: Stable department identity

## Status

Proposed — 2026-07-14.

## Context

Departements adalah master unit kerja untuk pengelompokan employee, position, headcount, dan reporting. Risiko utama adalah penghapusan master yang masih direferensikan; perubahan kode setelah dipakai downstream. Keputusan boundary harus stabil sebelum module menjadi sumber data Attendance, Payroll, atau consumer lain.

## Decision

Kode departement menjadi identitas bisnis stabil; perubahan nama tidak boleh memutus relasi downstream.

Master tetap dimiliki `HR/Departements`. Consumer memakai reference/service/contract versioned, tidak mengubah tabel internal. Mutation wajib authorization, transaction bila menyentuh relasi, audit aman, dan referential guard.

## Alternatives considered

### Menyalin master ke setiap consumer

Ditolak karena menghasilkan drift dan arti code yang berbeda.

### Mengakses model/table secara langsung lintas project

Ditolak karena coupling schema dan lifecycle terlalu kuat.

### Menaruh seluruh aturan consumer pada master

Ditolak karena budget/cost center, department head effective-dated, merger/split workflow, organization chart tetap milik domain terkait.

## Consequences

- Code/reference harus diperlakukan sebagai contract bisnis.
- Perubahan yang berdampak retroaktif membutuhkan migration/approval eksplisit.
- Consumer bertanggung jawab atas behavior domainnya sendiri.
- ADR baru diperlukan bila ownership, effective dating, atau topology integrasi berubah.

## Approval questions

- Apakah keputusan identity/ownership ini diterima?
- Apakah force-delete harus dilarang setelah reference pertama?
- Consumer pertama mana yang membutuhkan read contract v1?
