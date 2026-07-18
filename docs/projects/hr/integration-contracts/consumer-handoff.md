# HR Integration Contracts: Consumer Handoff

Dokumen ini adalah panduan untuk module consumer yang akan memakai data HR, terutama `Attendance` dan `Payroll`. Prinsipnya sederhana: consumer boleh membaca contract/snapshot resmi, tetapi tidak boleh mengambil data langsung dari tabel/model internal HR secara bebas.

## Status

`Accepted for MVP handoff — 2026-07-18`

Boundary ini berlaku untuk development berikutnya:

- Attendance MVP.
- Payroll MVP.
- Consumer lain seperti Accounting/CRM hanya boleh mengikuti pola ini setelah punya spec sendiri.

## Urutan baca

1. [Specification](specification.md) — daftar snapshot/event v1 dan privacy boundary.
2. [ADR-001](decisions/001-stable-hr-integration-contracts.md) — alasan consumer memakai contract, bukan table coupling.
3. [Tasks](tasks.md) — status implementasi contract dan checkpoint.
4. Dokumen ini — cara Attendance/Payroll memakai contract secara aman.

## Aturan umum consumer

Consumer wajib:

- memakai provider/interface dari `App\Modules\HR\IntegrationContracts\Contracts`;
- mengirim tanggal acuan eksplisit (`asOf`, `effectiveDate`, atau business date);
- menyimpan snapshot sendiri jika membutuhkan reproducibility period;
- memperlakukan field contract v1 sebagai additive dan versioned;
- gagal tertutup jika contract tidak tersedia atau payload tidak valid.

Consumer tidak boleh:

- query langsung ke model/tabel internal HR untuk flow bisnis utama;
- membaca field personal/sensitif di luar contract;
- mengubah data HR dari listener consumer;
- membuat listener downstream spekulatif sebelum lifecycle consumer disetujui;
- memakai event HR sebagai satu-satunya sumber kebenaran tanpa snapshot/dry-run validation.

## Contract yang tersedia

| Contract | Tipe | Kegunaan utama |
|---|---|---|
| `EmployeeSnapshotV1` | Snapshot | Label employee, nomor employee, work email, active flag, linked user. |
| `EmployeeAssignmentSnapshotV1` | Snapshot | Departement, position, job level, work location, employment status/type pada tanggal acuan. |
| `EmployeeContractSnapshotV1` | Snapshot | Ringkasan kontrak aktif/terbaru yang aman untuk validasi employment type/period. |
| `EmployeeDocumentComplianceSnapshotV1` | Snapshot | Ringkasan compliance dokumen tanpa document number, DMS reference, path, URL, token, atau notes. |
| `IntegrationEventEnvelopeV1` | Event envelope | Bentuk standar event HR versioned. |

## Attendance handoff

Attendance memakai HR sebagai sumber employee eligibility dan assignment. Attendance tidak menjadi master employee.

### Input contract wajib

Untuk schedule generation, check-in/out, anomaly flagging, dan period closing, Attendance minimal membaca:

- `EmployeeSnapshotV1`
  - `employeeId`
  - `employeeNumber`
  - `displayName`
  - `workEmail`
  - `isActive`
  - `linkedUserId`
- `EmployeeAssignmentSnapshotV1`
  - `effectiveDate`
  - `departement`
  - `position`
  - `jobLevel`
  - `workLocation`
  - `employmentStatus.requiresAttendance`
  - `employmentStatus.isTerminal`
  - `employmentType.eligibleForOvertime`

### Semantics Attendance

- `isActive=false` berarti employee tidak eligible untuk schedule baru.
- `employmentStatus.requiresAttendance=false` berarti Attendance wajib menolak schedule/check-in reguler untuk tanggal acuan tersebut.
- `employmentStatus.isTerminal=true` berarti employee tidak boleh diproses untuk attendance period setelah tanggal terminal/effective exit.
- `workLocation` adalah referensi lokasi kerja. Detail geofence tetap mengikuti Work Location contract/field yang sudah tersedia di HR, tetapi consumer tidak boleh menyimpan ulang master HR sebagai sumber kebenaran.
- `employmentType.eligibleForOvertime=false` berarti overtime request/approval harus ditolak atau ditandai tidak eligible.

### Event yang boleh dipantau nanti

Attendance boleh merencanakan listener untuk event berikut, tetapi MVP Integration Contracts belum memasang listener downstream:

- `EmployeeCreatedV1`
- `EmployeeProfileUpdatedV1`
- `EmployeeAssignmentChangedV1`
- `EmployeeOffboardingFinalizedV1`
- `EmploymentTerminatedV1`

Jika listener dibuat nanti, listener hanya boleh:

- refresh cache/snapshot Attendance;
- invalidate eligibility;
- membuat alert non-mutating bila data tidak konsisten.

Listener tidak boleh:

- mengubah employee HR;
- mengubah kontrak HR;
- melakukan payroll mutation;
- menerima event tanpa validasi envelope.

### Snapshot rule untuk Attendance

Saat `AttendancePeriods` ditutup, Attendance wajib menyimpan snapshot final yang dibaca dari HR Integration Contracts. Setelah period locked, perubahan HR berikutnya tidak boleh mengubah hasil periode yang sudah ditutup.

## Payroll handoff

Payroll memakai HR untuk identitas operasional, assignment, status/type kerja, contract state, dan effective termination. Payroll tidak boleh menghitung dari data HR terbaru secara langsung jika sedang memproses periode historis.

### Input contract wajib

Untuk import snapshot, calculate run, validate run, payslip, dan posting, Payroll minimal membaca:

- `EmployeeSnapshotV1`
  - `employeeId`
  - `employeeNumber`
  - `displayName`
  - `isActive`
- `EmployeeAssignmentSnapshotV1`
  - `departement`
  - `position`
  - `jobLevel`
  - `workLocation`
  - `employmentStatus.includedInPayroll`
  - `employmentStatus.isTerminal`
  - `employmentType.includedInPayroll`
  - `employmentType.requiresContractEndDate`
- `EmployeeContractSnapshotV1`
  - `contractId`
  - `contractType`
  - `status`
  - `startDate`
  - `endDate`
  - `isCurrent`
- `EmployeeDocumentComplianceSnapshotV1`
  - hanya jika payroll policy membutuhkan compliance gate sebelum payment.

### Semantics Payroll

- `employmentStatus.includedInPayroll=false` berarti employee tidak boleh masuk payroll regular untuk tanggal acuan.
- `employmentType.includedInPayroll=false` berarti payroll wajib skip employee, kecuali ada input manual yang disetujui dalam Payroll sendiri.
- `employmentStatus.isTerminal=true` berarti payroll harus mengecek tanggal efektif exit/offboarding dan hanya memproses hak final sesuai policy Payroll.
- `requiresContractEndDate=true` berarti Payroll perlu memastikan `EmployeeContractSnapshotV1.endDate` tersedia jika policy kontrak membutuhkannya.
- `EmployeeContractSnapshotV1.isCurrent=false` berarti snapshot adalah fallback kontrak terbaru, bukan kontrak aktif pada tanggal acuan. Payroll harus memperlakukan ini sebagai warning/validation issue, bukan silently valid.

### Event yang boleh dipantau nanti

Payroll boleh merencanakan listener untuk event berikut, tetapi belum dibuat pada MVP:

- `EmployeeCreatedV1`
- `EmployeeProfileUpdatedV1`
- `EmployeeAssignmentChangedV1`
- `EmployeeContractChangedV1`
- `EmployeeOffboardingFinalizedV1`
- `EmploymentTerminatedV1`

Listener Payroll nanti hanya boleh:

- invalidate/import ulang snapshot sebelum payroll run locked;
- menandai payroll input perlu review;
- menolak calculate jika snapshot HR tidak valid.

Listener Payroll tidak boleh:

- mengubah data HR;
- mengubah payroll run yang sudah approved/locked;
- membuat payment/posting otomatis dari event HR;
- membaca salary/compensation dari HR Integration Contracts v1 karena field itu sengaja tidak tersedia.

### Snapshot rule untuk Payroll

`PayrollInputs` wajib menyimpan snapshot HR dan Attendance yang dipakai saat run dibuat. Setelah payroll run approved, perubahan HR berikutnya hanya boleh masuk lewat reversal/adjustment run, bukan mengubah hasil lama.

## Field yang dilarang untuk consumer

Field berikut tidak boleh dipakai oleh Attendance/Payroll dari HR Integration Contracts v1:

- password, remember token, reset token/link;
- NIK/KTP/NPWP/national id;
- tanggal lahir;
- alamat pribadi;
- personal phone;
- emergency contact;
- bank account;
- salary/compensation;
- document number;
- DMS reference/path/URL/token;
- notes internal/confidential;
- raw file metadata.

Jika consumer membutuhkan salah satu field di atas, buat spec dan ADR baru. Jangan menambahkan field sensitif ke contract v1 secara diam-diam.

## Deferred items

Item berikut sengaja ditunda:

- queue/outbox production delivery;
- retry/order guarantee lintas service;
- listener downstream Attendance;
- listener downstream Payroll;
- public REST API/webhook;
- integration event log table;
- data warehouse/report mart;
- compensation/payroll profile contract;
- cost center/accounting journal contract.

## Command untuk developer

Gunakan command berikut sebelum mulai membuat consumer:

```bash
php artisan hr:integration-contracts:describe
php artisan hr:integration-contracts:validate
php artisan hr:integration-contracts:sample 1 --date=2026-07-18
```

Untuk output machine-readable:

```bash
php artisan hr:integration-contracts:describe --json
```

## Acceptance handoff untuk Attendance MVP

- Attendance membaca `EmployeeAssignmentSnapshotProvider`, bukan model `Employee` langsung.
- Attendance menolak schedule/check-in jika `requiresAttendance=false`.
- Attendance menyimpan snapshot final saat period closing.
- Attendance tests mencakup employee inactive, status terminal, non-attendance status, dan overtime ineligible.

## Acceptance handoff untuk Payroll MVP

- Payroll membaca `EmployeeSnapshotProvider`, `EmployeeAssignmentSnapshotProvider`, dan `EmployeeContractSnapshotProvider`.
- Payroll menolak/flag employee jika `includedInPayroll=false`.
- Payroll memperlakukan `isCurrent=false` contract snapshot sebagai validation warning.
- Payroll menyimpan snapshot input saat payroll run dibuat.
- Payroll tests mencakup terminated employee, contract expired/missing, non-payroll status/type, dan locked run immutability.
