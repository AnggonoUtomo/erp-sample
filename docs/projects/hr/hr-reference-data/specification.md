# Specification: HR Reference Data

## Status

Draft — 2026-07-14. Dokumen memetakan implementation aktual dan target hardening. Persetujuan manusia diperlukan sebelum task yang mengubah behavior/schema dimulai.

## Objective

Menetapkan `HR/HRReferenceData` sebagai catalog referensi umum HR yang terkelompok dan dapat dipakai konsisten oleh modul lain. Pengguna utama adalah HR manager/officer; viewer memperoleh akses baca sesuai permission.

## Scope

- CRUD/list master reference category dan reference item dengan pagination/filter sesuai UI aktual.
- Data contract: category code/name/description serta item category, code, name, metadata, active, sort_order, timestamps, dan lifecycle.
- Validasi code/relasi, authorization policy, audit, dan lifecycle yang konsisten.
- Menyediakan pilihan/reference aman bagi consumer tanpa menduplikasi master.

## Non-scope

master khusus domain yang memiliki behavior sendiri, translation engine, arbitrary application configuration.

## Relasi dan ownership

Menjadi upstream Employees, Employee Documents, Attendance, Payroll, dan consumer HR lain melalui metadata category/code.

Module ini memiliki master dan invariannya sendiri. Consumer tidak boleh mengubah tabel secara langsung; perubahan lintas module memakai service/contract versioned bila coupling mulai meluas.

## Route dan permission baseline

GET `/hr/hr-reference-data`; CRUD category dan item; restore/force-delete item.

Permission: `hr-reference-data.view/create/update/delete/restore/force-delete/manage`. Semua mutation wajib authenticated dan diperiksa policy/permission server-side.

## Struktur target

```txt
app/Modules/HR/HRReferenceData/
  Database/ DTO/ Http/ Models/ Policies/ Providers/
  Services/ Support/ Transactions/
  module.php routes.php permissions.php navigation.php
resources/js/pages/hr/hr-reference-data/
tests/Feature/HRHRReferenceDataTest.php
docs/projects/hr/hr-reference-data/
```

## Commands

```bash
php artisan module:validate
php artisan test --filter=HRReferenceData
vendor/bin/pint --test
npm run lint:check
npm run format:check
npm run typecheck
npm run test:frontend
npm run build
php artisan test
git diff --check
```

## Boundaries

- **Always:** FormRequest → DTO → Service → Transaction → Model, policy server-side, pagination, audit mutation, dan regression test.
- **Ask first:** schema/index/foreign key baru, public event/contract, perubahan code yang sudah direferensikan, force delete, atau perubahan ownership.
- **Never:** hard-coded authorization di UI saja, direct table mutation oleh consumer, hard delete data yang direferensikan, atau menyimpan secret/PII pada audit log.

## Acceptance criteria

- Contract module valid dan seluruh route mutation authenticated.
- Code/relasi unik dan input invalid tidak mengubah database.
- Permission denial mencakup create/update/delete/restore/force-delete yang tersedia.
- Lifecycle tidak merusak referensi downstream.
- List aman, paginated, dan tidak mengekspos data di luar kebutuhan.
- Dokumentasi dan executable tests sesuai implementation aktual.

## Risiko utama

category/code diubah setelah dipakai; metadata tanpa schema; duplikasi master khusus.

## Open questions

- Apakah code boleh diubah setelah dipakai consumer?
- Apakah force-delete tetap diperlukan atau harus dinonaktifkan?
- Contract read model/event apa yang dibutuhkan Attendance dan Payroll?
- Apakah perubahan master perlu effective date dan approval?
