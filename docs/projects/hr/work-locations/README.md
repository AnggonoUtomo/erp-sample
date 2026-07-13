# Work Locations

Project document ini menjelaskan baseline, batas domain, dan rencana pengembangan module `HR/WorkLocations`. Fungsi utamanya adalah master lokasi kerja fisik beserta koordinat/geofence untuk employee assignment dan Attendance.

## Status

`As-built baseline documented; specification Draft dan ADR-001 Proposed — 2026-07-14`.

Kode CRUD dasar sudah tersedia. Dokumen ini tidak menyatakan seluruh hardening/future scope telah diimplementasikan dan tidak mengizinkan perubahan schema sebelum specification/ADR disetujui.

## Urutan baca

1. [Specification](specification.md) — requirement, non-scope, contract data, boundary, dan acceptance criteria.
2. [ADR-001](decisions/001-location-owns-geospatial-reference-attendance-owns-events.md) — keputusan domain utama yang perlu disetujui.
3. [Implementation plan](implementation-plan.md) — dependency graph, tahapan, risiko, dan checkpoint.
4. [Tasks](tasks.md) — vertical slice kecil, file scope, acceptance criteria, dan test.

## Output module

- Master resmi untuk work location.
- Data tervalidasi dan permission server-side untuk consumer HR.
- Lifecycle aman melalui active/soft-delete sesuai kemampuan schema aktual.
- Referensi stabil untuk modul downstream tanpa menyalin master.

## Relasi

Menjadi upstream Employees, Employee Movements, Attendance, Payroll, dan reporting.

Lihat juga [HR project index](../README.md), [HR module guide](../module-guide.md), dan [HR lifecycle guide](../user-guide/README.md).

## Verifikasi baseline

```bash
php artisan module:validate
php artisan test --filter=HRWorkLocation
vendor/bin/pint --test
npm run quality:check
```
