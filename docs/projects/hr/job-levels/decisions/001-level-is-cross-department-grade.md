# ADR-001: Level is cross-department grade

## Status

Proposed — 2026-07-14.

## Context

Job Levels adalah master level/grade jabatan lintas departement untuk career, approval, benefit, dan payroll band. Risiko utama adalah urutan level ambigu; penghapusan grade yang direferensikan; pencampuran grade dan position. Keputusan boundary harus stabil sebelum module menjadi sumber data Attendance, Payroll, atau consumer lain.

## Decision

Job level dipisahkan dari position agar grade yang sama dapat dipakai lintas unit dan tidak berubah ketika nama jabatan berubah.

Master tetap dimiliki `HR/JobLevels`. Consumer memakai reference/service/contract versioned, tidak mengubah tabel internal. Mutation wajib authorization, transaction bila menyentuh relasi, audit aman, dan referential guard.

## Alternatives considered

### Menyalin master ke setiap consumer

Ditolak karena menghasilkan drift dan arti code yang berbeda.

### Mengakses model/table secara langsung lintas project

Ditolak karena coupling schema dan lifecycle terlalu kuat.

### Menaruh seluruh aturan consumer pada master

Ditolak karena salary band nominal, career path workflow, promotion approval, performance grade tetap milik domain terkait.

## Consequences

- Code/reference harus diperlakukan sebagai contract bisnis.
- Perubahan yang berdampak retroaktif membutuhkan migration/approval eksplisit.
- Consumer bertanggung jawab atas behavior domainnya sendiri.
- ADR baru diperlukan bila ownership, effective dating, atau topology integrasi berubah.

## Approval questions

- Apakah keputusan identity/ownership ini diterima?
- Apakah force-delete harus dilarang setelah reference pertama?
- Consumer pertama mana yang membutuhkan read contract v1?
