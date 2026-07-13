# ADR-001: Position belongs to one department

## Status

Proposed — 2026-07-14.

## Context

Positions adalah master jabatan/job title dalam departement untuk assignment employee dan kebutuhan organisasi. Risiko utama adalah position dipindah departement secara retroaktif; delete saat direferensikan; code collision. Keputusan boundary harus stabil sebelum module menjadi sumber data Attendance, Payroll, atau consumer lain.

## Decision

Baseline mengikat position ke satu departement agar assignment tervalidasi; kebutuhan position lintas departement memerlukan desain baru.

Master tetap dimiliki `HR/Positions`. Consumer memakai reference/service/contract versioned, tidak mengubah tabel internal. Mutation wajib authorization, transaction bila menyentuh relasi, audit aman, dan referential guard.

## Alternatives considered

### Menyalin master ke setiap consumer

Ditolak karena menghasilkan drift dan arti code yang berbeda.

### Mengakses model/table secara langsung lintas project

Ditolak karena coupling schema dan lifecycle terlalu kuat.

### Menaruh seluruh aturan consumer pada master

Ditolak karena position occupancy/headcount plan, job description versioning, competency model, salary band tetap milik domain terkait.

## Consequences

- Code/reference harus diperlakukan sebagai contract bisnis.
- Perubahan yang berdampak retroaktif membutuhkan migration/approval eksplisit.
- Consumer bertanggung jawab atas behavior domainnya sendiri.
- ADR baru diperlukan bila ownership, effective dating, atau topology integrasi berubah.

## Approval questions

- Apakah keputusan identity/ownership ini diterima?
- Apakah force-delete harus dilarang setelah reference pertama?
- Consumer pertama mana yang membutuhkan read contract v1?
