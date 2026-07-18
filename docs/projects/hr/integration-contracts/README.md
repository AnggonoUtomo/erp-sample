# HR Integration Contracts

Dokumen ini mendefinisikan project kecil `HR/IntegrationContracts` sebagai payung kontrak antar modul. Ini bukan menu baru untuk user, bukan lifecycle baru, dan bukan tempat membuat data HR. Tujuannya adalah menyediakan cara baca/event yang stabil agar project lain seperti Attendance, Payroll, Accounting, CRM, Document Management, dan Reports tidak perlu mengambil data langsung dari tabel internal HR.

## Status

`Checkpoint A complete — read-only snapshot foundation selesai pada 2026-07-18`.

Dokumen ini dibuat setelah MVP HR Reports selesai agar langkah berikutnya tidak langsung membuat integrasi spekulatif. Specification dan ADR-001 sudah disetujui. Implementasi dimulai dari module shell + registry tanpa UI, route, migration, atau permission user baru.

## Urutan baca

1. [Specification](specification.md) — requirement, non-scope, contract shape, privacy boundary, command design, acceptance criteria, dan test plan.
2. [ADR-001: Stable HR integration contracts](decisions/001-stable-hr-integration-contracts.md) — alasan kontrak dibuat sebagai boundary, bukan akses tabel langsung.
3. [Implementation plan](implementation-plan.md) — urutan vertical slice, dependency, risiko, dan checkpoint.
4. [Tasks](tasks.md) — task kecil yang nanti bisa dieksekusi satu per satu.

## Relasi lintas dokumen

- [HR module guide](../module-guide.md) — aturan umum module HR, permission, frontend, backend, dan quality gates.
- [HR roadmap](../roadmap.md) — posisi Integration Contracts setelah HR Reports dan sebelum Attendance/Payroll.
- [Employee Contracts](../employee-contracts/README.md) — sumber contract state dan contract expiry.
- [Employee Movements](../employee-movements/README.md) — sumber perubahan work profile dan applied movement event.
- [Employee Documents](../employee-documents/README.md) — sumber metadata dokumen dan boundary DMS.
- [Onboardings](../onboardings/README.md) — sumber event onboarding lifecycle.
- [Offboardings](../offboardings/README.md) — sumber event effective exit/termination.
- [HR Reports](../hr-reports/README.md) — consumer read-only internal HR yang membaca data tanpa mutation.

## Bentuk final MVP

MVP Integration Contracts hanya menyediakan:

- snapshot aman untuk employee identity/work assignment;
- event v1 untuk perubahan penting HR;
- registry/schema contract agar consumer tahu field yang boleh dipakai;
- contract tests untuk memastikan payload stabil dan tidak membawa data sensitif.

Task 01 sudah menyediakan registry awal untuk daftar snapshot/event v1. Task 02 menambahkan DTO snapshot/event envelope dan forbidden-field privacy guard. Task 03 menambahkan `EmployeeSnapshotProvider` read-only untuk identitas operasional minimal employee. Task 04 menambahkan `EmployeeAssignmentSnapshotProvider` read-only untuk current work profile pada tanggal acuan eksplisit. Contract/document compliance provider, command inspeksi, dan event publisher mapping dikerjakan pada task berikutnya.

Checkpoint A sudah membuktikan foundation ini tetap read-only: tidak ada UI/menu, route, migration, permission user-facing, atau mutation/write pattern di module Integration Contracts.

MVP tidak membuat:

- halaman menu baru;
- table integration warehouse;
- queue listener downstream;
- webhook eksternal;
- API publik untuk aplikasi luar;
- sinkronisasi Attendance/Payroll otomatis.

## Prinsip singkat

- Consumer tidak membaca tabel internal HR secara bebas.
- Contract bersifat additive dan versioned.
- Payload default harus minim PII.
- Event hanya dipublikasikan untuk perubahan yang sudah commit/berhasil.
- Tidak ada consumer downstream spekulatif sampai modul consumer dibuat.
