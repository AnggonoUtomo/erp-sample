# ADR-001: Type exposes capability flags

## Status

Proposed — 2026-07-14.

## Context

Employment Types adalah master tipe hubungan kerja untuk kebutuhan kontrak, benefit, overtime, dan payroll. Risiko utama adalah flag berubah retroaktif; kontrak tanpa end date untuk tipe yang mewajibkannya. Keputusan boundary harus stabil sebelum module menjadi sumber data Attendance, Payroll, atau consumer lain.

## Decision

Tipe kerja menyediakan capability tingkat master; kalkulasi dan pengecualian spesifik tetap berada di modul pemilik aturan.

Master tetap dimiliki `HR/EmploymentTypes`. Consumer memakai reference/service/contract versioned, tidak mengubah tabel internal. Mutation wajib authorization, transaction bila menyentuh relasi, audit aman, dan referential guard.

## Alternatives considered

### Menyalin master ke setiap consumer

Ditolak karena menghasilkan drift dan arti code yang berbeda.

### Mengakses model/table secara langsung lintas project

Ditolak karena coupling schema dan lifecycle terlalu kuat.

### Menaruh seluruh aturan consumer pada master

Ditolak karena contract document, benefit enrollment, overtime formula, payroll component tetap milik domain terkait.

## Consequences

- Code/reference harus diperlakukan sebagai contract bisnis.
- Perubahan yang berdampak retroaktif membutuhkan migration/approval eksplisit.
- Consumer bertanggung jawab atas behavior domainnya sendiri.
- ADR baru diperlukan bila ownership, effective dating, atau topology integrasi berubah.

## Approval questions

- Apakah keputusan identity/ownership ini diterima?
- Apakah force-delete harus dilarang setelah reference pertama?
- Consumer pertama mana yang membutuhkan read contract v1?
