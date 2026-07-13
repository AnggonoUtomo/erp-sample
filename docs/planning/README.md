# Project Delivery Map

Dokumen ini menjadi pintu masuk pekerjaan setelah HR Core. Implementasi wajib mengikuti urutan dependensi, bukan sekadar urutan menu.

## Urutan baca dan implementasi

1. [Attendance](attendance.md) — memakai master employee, lokasi, status, dan supervisor dari HR.
2. [Payroll](payroll.md) — memakai snapshot HR dan hasil attendance yang sudah ditutup.
3. [Accounting](accounting.md) — menerima payroll posting berupa journal summary, tanpa membaca PII employee.
4. [CRM](crm.md) — domain customer-facing yang independen dari payroll, tetapi memakai kontrak organisasi dan dokumen bersama.
5. [Document Management](document-management.md) — mesin dokumen lintas proyek; metadata HR/CRM tetap dimiliki domain asal.

## Aturan lintas proyek

- Setiap project berada di `app/Modules/<Project>/<Submodule>` dan frontend di `resources/js/pages/<project>/<submodule>`.
- Integrasi lintas project memakai contract/snapshot/event; dilarang mengakses model internal project lain dari business logic.
- Setiap mutation wajib FormRequest, Policy, permission denial test, audit log, dan transaction boundary.
- Modul pertama setiap project harus berupa vertical slice kecil yang tetap melewati validator, lint, typecheck, build, dan backend test.
- Status dokumen ini adalah specification for next job; belum mengotorisasi migration atau implementasi kelima project.

## Commands standar

```bash
php artisan module:validate
php artisan test
vendor/bin/pint --test
npm run lint
npm run format:check
npm run types
npm run build
```
