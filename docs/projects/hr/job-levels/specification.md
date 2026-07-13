# Specification: Job Levels

## Status

Draft — 2026-07-14. Dokumen memetakan implementation aktual dan target hardening. Persetujuan manusia diperlukan sebelum task yang mengubah behavior/schema dimulai.

## Objective

Menetapkan `HR/JobLevels` sebagai master level/grade jabatan lintas departement untuk career, approval, benefit, dan payroll band. Pengguna utama adalah HR manager/officer; viewer memperoleh akses baca sesuai permission.

## Scope

- CRUD/list master job level dengan pagination/filter sesuai UI aktual.
- Data contract: code, name, description, active, sort_order, timestamps, dan soft delete.
- Validasi code/relasi, authorization policy, audit, dan lifecycle yang konsisten.
- Menyediakan pilihan/reference aman bagi consumer tanpa menduplikasi master.

## Non-scope

salary band nominal, career path workflow, promotion approval, performance grade.

## Relasi dan ownership

Menjadi upstream Employees, Positions/career mapping, Employee Movements, Benefits, dan Payroll.

Module ini memiliki master dan invariannya sendiri. Consumer tidak boleh mengubah tabel secara langsung; perubahan lintas module memakai service/contract versioned bila coupling mulai meluas.

## Route dan permission baseline

GET/POST `/hr/job-levels`; PUT/DELETE, PATCH restore, DELETE force per level.

Permission: `job-levels.view/create/update/delete/restore/force-delete/manage`. Semua mutation wajib authenticated dan diperiksa policy/permission server-side.

## Struktur target

```txt
app/Modules/HR/JobLevels/
  Database/ DTO/ Http/ Models/ Policies/ Providers/
  Services/ Support/ Transactions/
  module.php routes.php permissions.php navigation.php
resources/js/pages/hr/job-levels/
tests/Feature/HRJobLevelsTest.php
docs/projects/hr/job-levels/
```

## Commands

```bash
php artisan module:validate
php artisan test --filter=HRJobLevel
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

urutan level ambigu; penghapusan grade yang direferensikan; pencampuran grade dan position.

## Open questions

- Apakah code boleh diubah setelah dipakai consumer?
- Apakah force-delete tetap diperlukan atau harus dinonaktifkan?
- Contract read model/event apa yang dibutuhkan Attendance dan Payroll?
- Apakah perubahan master perlu effective date dan approval?
