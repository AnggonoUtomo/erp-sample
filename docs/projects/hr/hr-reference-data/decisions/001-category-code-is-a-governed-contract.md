# ADR-001: Category code is a governed contract

## Status

Proposed — 2026-07-14.

## Context

HR Reference Data adalah catalog referensi umum HR yang terkelompok dan dapat dipakai konsisten oleh modul lain. Risiko utama adalah category/code diubah setelah dipakai; metadata tanpa schema; duplikasi master khusus. Keputusan boundary harus stabil sebelum module menjadi sumber data Attendance, Payroll, atau consumer lain.

## Decision

Consumer bergantung pada category/code, bukan label atau ID hard-coded, sehingga label dapat berubah tanpa merusak contract.

Master tetap dimiliki `HR/HRReferenceData`. Consumer memakai reference/service/contract versioned, tidak mengubah tabel internal. Mutation wajib authorization, transaction bila menyentuh relasi, audit aman, dan referential guard.

## Alternatives considered

### Menyalin master ke setiap consumer

Ditolak karena menghasilkan drift dan arti code yang berbeda.

### Mengakses model/table secara langsung lintas project

Ditolak karena coupling schema dan lifecycle terlalu kuat.

### Menaruh seluruh aturan consumer pada master

Ditolak karena master khusus domain yang memiliki behavior sendiri, translation engine, arbitrary application configuration tetap milik domain terkait.

## Consequences

- Code/reference harus diperlakukan sebagai contract bisnis.
- Perubahan yang berdampak retroaktif membutuhkan migration/approval eksplisit.
- Consumer bertanggung jawab atas behavior domainnya sendiri.
- ADR baru diperlukan bila ownership, effective dating, atau topology integrasi berubah.

## Approval questions

- Apakah keputusan identity/ownership ini diterima?
- Apakah force-delete harus dilarang setelah reference pertama?
- Consumer pertama mana yang membutuhkan read contract v1?
