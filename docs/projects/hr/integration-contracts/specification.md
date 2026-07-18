# Specification: HR Integration Contracts

## 1. Objective

Membuat boundary kontrak HR yang stabil agar modul lain dapat memakai data employee, assignment, contract, document, onboarding, offboarding, dan movement tanpa bergantung pada struktur database internal HR.

Keberhasilan MVP berarti developer bisa membangun Attendance/Payroll/CRM berikutnya dengan membaca contract/snapshot/event yang jelas, bukan menebak field dari model Eloquent HR.

## 2. Assumptions

1. Project HR sudah menjadi sumber kebenaran untuk employee profile dan lifecycle HR.
2. Consumer awal adalah module internal aplikasi yang berjalan dalam monolith Laravel yang sama.
3. Belum ada kebutuhan API publik lintas aplikasi/server pada MVP.
4. Attendance dan Payroll akan membutuhkan employee active state, assignment, employment type/status, work location, dan effective date.
5. HR Reports tetap read-only dan tidak menjadi publisher event.
6. Payload integration tidak boleh membawa PII bebas kecuali disetujui secara eksplisit per consumer.
7. Contract v1 harus kompatibel dengan modul existing yang sudah punya folder `Integration`.

## 3. Scope MVP

### 3.1 Contract read models

MVP menyediakan contract berikut:

1. `EmployeeSnapshotV1`
   - Identitas operasional minimal employee.
   - Dipakai untuk label employee, referensi assignment, dan validation consumer.

2. `EmployeeAssignmentSnapshotV1`
   - Work profile employee pada tanggal acuan tertentu.
   - Dipakai Attendance/Payroll untuk menentukan departement, position, job level, work location, employment status, dan employment type.

3. `EmployeeContractSnapshotV1`
   - Ringkasan kontrak aktif/terbaru yang aman.
   - Dipakai Payroll atau HR Reports lanjutan untuk validasi tipe kerja dan masa berlaku.

4. `EmployeeDocumentComplianceSnapshotV1`
   - Ringkasan compliance dokumen, bukan file/reference DMS.
   - Dipakai sebagai status kelengkapan dokumen tanpa membuka storage.

### 3.2 Event contracts

MVP mendefinisikan event payload v1 berikut:

1. `EmployeeCreatedV1`
2. `EmployeeProfileUpdatedV1`
3. `EmployeeAssignmentChangedV1`
4. `EmployeeContractChangedV1`
5. `EmployeeDocumentComplianceChangedV1`
6. `EmployeeOnboardingActivatedV1`
7. `EmployeeOnboardingCompletedV1`
8. `EmployeeOffboardingReadyV1`
9. `EmployeeOffboardingFinalizedV1`
10. `EmploymentTerminatedV1`

Event MVP hanya contract dan publisher internal HR. Listener downstream Attendance/Payroll ditunda sampai modul consumer dikerjakan.

### 3.3 Consumer registry

Consumer awal yang perlu didokumentasikan:

| Consumer | Kebutuhan data | Mode MVP |
|---|---|---|
| Attendance | active employee, work location, schedule eligibility, employment status/type | Deferred consumer |
| Payroll | active employee, employment type/status, contract state, effective termination | Deferred consumer |
| Accounting | payroll summary reference, employee cost center di fase lanjutan | Deferred |
| CRM | owner/salesperson employee identity minimal | Deferred |
| Document Management | owner context HR dan secure access handoff | Existing DMS boundary |
| HR Reports | read-only report dari HR data | Existing internal consumer |

## 4. Non-scope

MVP tidak mencakup:

- UI/menu baru untuk user.
- CRUD integration contract di database.
- Public REST API untuk aplikasi eksternal.
- Webhook external.
- Queue/outbox production delivery.
- Retry/order guarantee lintas service.
- Data warehouse/report mart.
- Sinkronisasi otomatis ke Attendance/Payroll.
- Listener yang mengubah data downstream.
- Export payload lengkap berisi PII.

Jika salah satu hal di atas diperlukan, buat ADR baru dan task terpisah.

## 5. Contract shape

### 5.1 Common envelope

Semua event memakai envelope standar:

```json
{
  "eventId": "uuid",
  "eventName": "EmployeeAssignmentChangedV1",
  "eventVersion": 1,
  "occurredAt": "2026-07-18T10:00:00+07:00",
  "sourceModule": "HR.EmployeeMovements",
  "actorUserId": 12,
  "correlationId": "optional-request-or-command-id",
  "payload": {}
}
```

Aturan:

- `eventId` wajib unik.
- `eventName` wajib eksplisit dan versioned.
- `occurredAt` memakai ISO-8601.
- `actorUserId` boleh null untuk system command/scheduler.
- `payload` tidak boleh memuat field yang tidak tercatat dalam schema contract.

### 5.2 EmployeeSnapshotV1

```json
{
  "employeeId": 1001,
  "employeeNumber": "EMP-001",
  "displayName": "Budi Santoso",
  "workEmail": "budi@example.test",
  "isActive": true,
  "linkedUserId": 21
}
```

Field yang tidak boleh masuk:

- NIK/KTP/NPWP;
- alamat pribadi;
- emergency contact;
- tanggal lahir;
- nomor telepon pribadi;
- bank account;
- notes internal.

### 5.3 EmployeeAssignmentSnapshotV1

```json
{
  "employeeId": 1001,
  "effectiveDate": "2026-07-18",
  "departement": {
    "id": 1,
    "code": "HR",
    "name": "Human Resources"
  },
  "position": {
    "id": 5,
    "code": "HR-OFFICER",
    "name": "HR Officer"
  },
  "jobLevel": {
    "id": 2,
    "code": "STAFF",
    "name": "Staff"
  },
  "workLocation": {
    "id": 3,
    "code": "HQ",
    "name": "Head Office"
  },
  "employmentStatus": {
    "id": 1,
    "code": "ACTIVE",
    "name": "Active",
    "requiresAttendance": true,
    "includedInPayroll": true,
    "isTerminal": false
  },
  "employmentType": {
    "id": 1,
    "code": "PERMANENT",
    "name": "Permanent",
    "requiresContractEndDate": false,
    "includedInPayroll": true,
    "eligibleForOvertime": true
  }
}
```

### 5.4 EmployeeContractSnapshotV1

```json
{
  "employeeId": 1001,
  "contractId": 88,
  "contractType": "PERMANENT",
  "status": "ACTIVE",
  "startDate": "2026-01-01",
  "endDate": null,
  "isCurrent": true
}
```

Tidak boleh mengirim compensation, notes, atau lampiran kontrak.

### 5.5 EmployeeDocumentComplianceSnapshotV1

```json
{
  "employeeId": 1001,
  "requiredCount": 4,
  "verifiedCount": 3,
  "pendingCount": 1,
  "expiredCount": 0,
  "expiringCount": 1,
  "asOf": "2026-07-18"
}
```

Tidak boleh mengirim document number, DMS reference, storage path, URL, token, atau file metadata internal.

## 6. Command design

Command non-mutating untuk inspeksi contract:

```bash
php artisan hr:integration-contracts:describe
php artisan hr:integration-contracts:validate
php artisan hr:integration-contracts:sample 1001 --date=2026-07-18
```

Design:

- `describe` menampilkan daftar contract dan versi.
- `validate` mengecek schema registry, forbidden fields, dan publisher mapping.
- `sample` mencetak contoh payload aman untuk satu employee bila datanya ada.
- Semua command tidak menulis database, audit, notification, queue, atau file.
- Exit code non-zero hanya untuk input invalid atau schema invalid.

## 7. Project structure

Rencana lokasi implementasi:

```txt
app/Modules/HR/IntegrationContracts/
  module.php
  permissions.php
  Providers/
    HRIntegrationContractsServiceProvider.php
  Contracts/
    EmployeeSnapshotProvider.php
    EmployeeAssignmentSnapshotProvider.php
    EmployeeContractSnapshotProvider.php
    EmployeeDocumentComplianceSnapshotProvider.php
  DTO/
    EmployeeSnapshotV1.php
    EmployeeAssignmentSnapshotV1.php
    EmployeeContractSnapshotV1.php
    EmployeeDocumentComplianceSnapshotV1.php
    IntegrationEventEnvelopeV1.php
  Events/
    HRIntegrationEventV1.php
  Console/Commands/
    DescribeHRIntegrationContractsCommand.php
    ValidateHRIntegrationContractsCommand.php
    SampleHRIntegrationContractsCommand.php
  Support/
    HRIntegrationContractRegistry.php
    HRIntegrationEventRegistry.php
    ForbiddenIntegrationFieldGuard.php

tests/Feature/
  HRIntegrationContractRegistryTest.php
  HRIntegrationSnapshotTest.php
  HRIntegrationEventPrivacyTest.php
  HRIntegrationCommandTest.php
```

Catatan: jika implementasi nanti lebih cocok ditempatkan pada `app/Modules/HR/Employees/Integration`, `EmployeeMovements/Integration`, atau module existing lain, contract registry tetap harus menjadi satu sumber dokumentasi.

## 8. Authorization dan privacy

Karena MVP tidak membuat UI/API publik, permission khusus user tidak wajib untuk runtime contract internal. Namun command inspeksi admin tetap harus dibatasi oleh permission console atau hanya tersedia untuk CLI trusted environment.

Forbidden fields harus dites:

- password;
- remember token;
- reset token/link;
- NIK/KTP/NPWP/document number;
- alamat pribadi;
- emergency contact;
- bank account;
- salary/compensation;
- DMS storage path/URL/token;
- notes internal/confidential.

## 9. Success criteria

- Contract registry mencatat semua contract v1 dan event v1 MVP.
- Snapshot provider dapat menghasilkan payload aman dari data employee existing.
- Event payload memakai envelope standar.
- Tests membuktikan tidak ada forbidden fields pada payload.
- Tests membuktikan contract bersifat read-only/non-mutating.
- Existing module contracts tetap kompatibel atau didaftarkan ke registry.
- Dokumentasi consumer boundary jelas untuk Attendance dan Payroll.

## 10. Test plan

```bash
php artisan test --filter=HRIntegration
php artisan module:validate
vendor/bin/pint --test app/Modules/HR/IntegrationContracts tests/Feature/HRIntegration*
npm run typecheck
npm run build
git diff --check
```

Jika task hanya backend dan tidak menyentuh frontend, `npm run typecheck` dan `npm run build` tetap dijalankan pada checkpoint besar, bukan wajib setiap micro task.

## 11. Open questions sebelum coding

1. Apakah MVP cukup internal PHP contract, atau perlu route JSON internal sejak awal?
2. Apakah event v1 langsung memakai Laravel event biasa, atau cukup DTO/schema registry dulu?
3. Apakah command `sample` boleh membaca employee production by id di CLI admin, atau hanya di local/dev?

Rekomendasi sementara: mulai dari internal PHP contract + registry + tests dulu. Route JSON dan queue/outbox ditunda.
