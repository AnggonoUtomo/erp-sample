# ADR-001: Employee identity versus assignment

## Status

Proposed — 2026-07-14.

## Context

Employees adalah system of record profile personal dan work profile employee yang menjadi upstream modul operasional. Risiko utama adalah PII exposure; duplicate national/employee identity; direct assignment edit bypassing movement history. Keputusan boundary harus stabil sebelum module menjadi sumber data Attendance, Payroll, atau consumer lain.

## Decision

Employee number dan identitas inti stabil; perubahan assignment historis harus melalui Employee Movements, bukan overwrite tanpa histori.

Master tetap dimiliki `HR/Employees`. Consumer memakai reference/service/contract versioned, tidak mengubah tabel internal. Mutation wajib authorization, transaction bila menyentuh relasi, audit aman, dan referential guard.

## Alternatives considered

### Menyalin master ke setiap consumer

Ditolak karena menghasilkan drift dan arti code yang berbeda.

### Mengakses model/table secara langsung lintas project

Ditolak karena coupling schema dan lifecycle terlalu kuat.

### Menaruh seluruh aturan consumer pada master

Ditolak karena contract lifecycle, movement workflow, document binary, payroll/attendance transaction, recruitment tetap milik domain terkait.

## Consequences

- Code/reference harus diperlakukan sebagai contract bisnis.
- Perubahan yang berdampak retroaktif membutuhkan migration/approval eksplisit.
- Consumer bertanggung jawab atas behavior domainnya sendiri.
- ADR baru diperlukan bila ownership, effective dating, atau topology integrasi berubah.

## Approval questions

- Apakah keputusan identity/ownership ini diterima?
- Apakah force-delete harus dilarang setelah reference pertama?
- Consumer pertama mana yang membutuhkan read contract v1?
