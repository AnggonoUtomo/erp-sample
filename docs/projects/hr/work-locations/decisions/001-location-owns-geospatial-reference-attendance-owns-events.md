# ADR-001: Location owns geospatial reference, Attendance owns events

## Status

Proposed — 2026-07-14.

## Context

Work Locations adalah master lokasi kerja fisik beserta koordinat/geofence untuk employee assignment dan Attendance. Risiko utama adalah koordinat/radius tidak valid; timezone salah; perubahan geofence memengaruhi evaluasi operasional. Keputusan boundary harus stabil sebelum module menjadi sumber data Attendance, Payroll, atau consumer lain.

## Decision

HR menyimpan lokasi dan geofence master; check-in, device, schedule, dan evaluasi kehadiran tetap milik Attendance.

Master tetap dimiliki `HR/WorkLocations`. Consumer memakai reference/service/contract versioned, tidak mengubah tabel internal. Mutation wajib authorization, transaction bila menyentuh relasi, audit aman, dan referential guard.

## Alternatives considered

### Menyalin master ke setiap consumer

Ditolak karena menghasilkan drift dan arti code yang berbeda.

### Mengakses model/table secara langsung lintas project

Ditolak karena coupling schema dan lifecycle terlalu kuat.

### Menaruh seluruh aturan consumer pada master

Ditolak karena attendance logs, map provider integration, route tracking, multi-polygon geofence, remote-work policy tetap milik domain terkait.

## Consequences

- Code/reference harus diperlakukan sebagai contract bisnis.
- Perubahan yang berdampak retroaktif membutuhkan migration/approval eksplisit.
- Consumer bertanggung jawab atas behavior domainnya sendiri.
- ADR baru diperlukan bila ownership, effective dating, atau topology integrasi berubah.

## Approval questions

- Apakah keputusan identity/ownership ini diterima?
- Apakah force-delete harus dilarang setelah reference pertama?
- Consumer pertama mana yang membutuhkan read contract v1?
