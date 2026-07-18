# HR Reports

Paket dokumen ini mendefinisikan module `HR/HRReports` sebelum implementasi dimulai. Module ini menjadi pusat laporan HR yang bersifat read-only untuk membaca data Employees, Employee Contracts, Employee Documents, Employee Movements, Onboardings, dan Offboardings tanpa mengubah state module sumber.

## Status

`Implementation started` — batas read-only, MVP scope, implementation plan, task breakdown, dan ADR-001 telah disetujui pada 2026-07-18. [Task 01 — Module dan read-only boundary](tasks.md#task-01--module-dan-read-only-boundary) telah selesai sebagai shell read-only awal.

## Scope MVP

MVP HR Reports hanya mencakup laporan operasional yang paling dibutuhkan HR:

1. Headcount by Departement.
2. Headcount by Work Location.
3. Employment Status Summary.
4. Contract Expiry.
5. Document Expiry.

Export Excel/PDF, queued large export, chart kompleks, custom report builder, dan dashboard analytics belum masuk MVP.

## Prinsip utama

- Report hanya membaca data; tidak membuat, mengubah, menghapus, approve, cancel, apply, archive, restore, atau trigger notification.
- Report membaca projection/query resmi dari module sumber, bukan mengambil alih lifecycle module sumber.
- Semua tanggal laporan harus eksplisit agar hasil report reproducible.
- Data sensitif seperti nomor dokumen, notes internal, alasan confidential, storage path, URL file, dan token tidak boleh tampil di report MVP.
- Seeder lifecycle HR dibuat setelah boundary report disetujui agar data uji realistis mengikuti satu alur HR end-to-end.

## Urutan baca

1. [Specification](specification.md) — objective, requirement, non-scope, report contract, command design, acceptance criteria, dan test plan.
2. [ADR-001: Read-only reporting boundary](decisions/001-read-only-reporting-boundary.md) — alasan HR Reports tidak memiliki lifecycle mutation dan hanya membaca dari module sumber.
3. [Implementation plan](implementation-plan.md) — vertical slice, dependency, risiko, dan checkpoint implementasi.
4. [Tasks](tasks.md) — unit kerja kecil lengkap dengan tujuan, file yang disentuh, acceptance criteria, dan cara test.

## Dokumen terkait

- [HR roadmap](../roadmap.md#phase-6-hr-reports) — posisi HR Reports dalam Phase 6.
- [HR module guide](../module-guide.md) — aturan module, permission, backend, frontend, soft delete, dan quality gates.
- [Employees](../employees/README.md) — sumber utama profile employee.
- [Employee Contracts](../employee-contracts/README.md) — sumber contract expiry.
- [Employee Documents](../employee-documents/README.md) — sumber document expiry.
- [Employee Movements](../employee-movements/README.md) — histori perubahan profile.
- [Onboardings](../onboardings/README.md) — sumber new hire/process progress untuk fase berikutnya.
- [Offboardings](../offboardings/README.md) — sumber resignation/termination untuk fase berikutnya.

## Gate sebelum coding

Gate berikut telah disetujui pada 2026-07-18:

- lima report MVP di atas adalah scope pertama;
- HR Reports bersifat read-only tanpa side effect;
- tanggal report wajib eksplisit di service/query dan command;
- export Excel/PDF ditunda sampai read model stabil;
- lifecycle seeder HR dibuat setelah vertical slice report pertama tersedia.
