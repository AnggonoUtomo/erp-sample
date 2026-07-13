# ADR-001: Status carries eligibility flags

## Status

Proposed — 2026-07-14.

## Context

Employment Statuses adalah master status lifecycle kerja dan eligibility dasar attendance/payroll. Risiko utama adalah perubahan flag berdampak lintas domain; final status diaktifkan tanpa transition policy. Keputusan boundary harus stabil sebelum module menjadi sumber data Attendance, Payroll, atau consumer lain.

## Decision

Eligibility dasar melekat pada status agar consumer memperoleh arti konsisten, tetapi aturan payroll/attendance rinci tetap milik domain consumer.

Master tetap dimiliki `HR/EmploymentStatuses`. Consumer memakai reference/service/contract versioned, tidak mengubah tabel internal. Mutation wajib authorization, transaction bila menyentuh relasi, audit aman, dan referential guard.

## Alternatives considered

### Menyalin master ke setiap consumer

Ditolak karena menghasilkan drift dan arti code yang berbeda.

### Mengakses model/table secara langsung lintas project

Ditolak karena coupling schema dan lifecycle terlalu kuat.

### Menaruh seluruh aturan consumer pada master

Ditolak karena state machine employee, termination workflow, payroll calculation rule, attendance policy detail tetap milik domain terkait.

## Consequences

- Code/reference harus diperlakukan sebagai contract bisnis.
- Perubahan yang berdampak retroaktif membutuhkan migration/approval eksplisit.
- Consumer bertanggung jawab atas behavior domainnya sendiri.
- ADR baru diperlukan bila ownership, effective dating, atau topology integrasi berubah.

## Approval questions

- Apakah keputusan identity/ownership ini diterima?
- Apakah force-delete harus dilarang setelah reference pertama?
- Consumer pertama mana yang membutuhkan read contract v1?
