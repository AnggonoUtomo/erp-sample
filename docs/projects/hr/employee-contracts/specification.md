# Specification: Employee Contracts

## 1. Objective

Membangun module `EmployeeContracts` untuk HR manager/officer agar dapat mencatat kontrak employee berdasarkan masa berlaku, menemukan kontrak aktif pada suatu tanggal, mencegah periode yang ambigu, dan menjaga histori untuk audit serta snapshot Payroll.

Keberhasilan berarti satu employee dapat memiliki riwayat kontrak berurutan tanpa kehilangan kontrak lama, sementara sistem dapat menentukan secara deterministik kontrak yang berlaku pada tanggal tertentu.

## 2. Assumptions

1. `Employees` dan `EmploymentTypes` tetap menjadi sumber master employee dan tipe hubungan kerja.
2. Satu employee boleh memiliki banyak kontrak sepanjang waktu, tetapi maksimal satu kontrak non-cancelled berlaku pada tanggal yang sama.
3. `end_date = null` berarti kontrak tanpa batas waktu dan menutup kemungkinan kontrak berikutnya sampai kontrak tersebut diakhiri/superseded.
4. Record yang sudah mulai berlaku tidak diubah secara destruktif; koreksi historis memerlukan aksi khusus dan audit.
5. Nominal gaji, benefit, tax, attachment, reminder, approval, dan e-signature tidak termasuk vertical slice pertama.
6. Semua tanggal kontrak adalah business date (`YYYY-MM-DD`), bukan timestamp dan tidak bergantung timezone.

## 3. Requirements

### 3.1 Functional

- Membuat, melihat, memperbarui draft, mengaktifkan, mengakhiri, membatalkan, mengarsipkan, dan memulihkan kontrak sesuai state dan permission.
- Menentukan kontrak employee yang berlaku pada tanggal tertentu dengan aturan `start_date <= date` dan (`end_date` kosong atau `end_date >= date`).
- Menolak rentang kontrak non-cancelled yang overlap untuk employee yang sama.
- Mewajibkan `end_date` ketika `EmploymentType.requires_contract_end_date = true`.
- Menyimpan `contract_number`, employee, employment type, start/end date, status, signed date opsional, probation end date opsional, dan notes internal.
- Menampilkan daftar paginated dengan search serta filter employee, employment type, status, dan expiry window.
- Mencatat audit create, update draft, activate, terminate, cancel, archive, dan restore.
- Menyediakan event internal setelah lifecycle transition berhasil.

### 3.2 State machine

```txt
DRAFT ──activate──> ACTIVE ──terminate/end-date──> ENDED
  │                    │
  └──cancel────────> CANCELLED
                       ▲
ACTIVE ──supersede─────┘ + kontrak pengganti
```

- `DRAFT`: dapat diedit dan belum dianggap berlaku.
- `ACTIVE`: sudah disahkan; field periode tidak diedit langsung.
- `ENDED`: selesai alami atau diakhiri dengan reason.
- `CANCELLED`: tidak pernah/tidak lagi dianggap berlaku dan tidak ikut overlap query.
- `SUPERSEDED` direpresentasikan sebagai status kontrak lama plus `superseded_by_id`; keputusan rinci ada pada [ADR-001](decisions/001-effective-dated-contracts.md).

### 3.3 Authorization

Permission awal:

```txt
employee-contracts.view
employee-contracts.create
employee-contracts.update-draft
employee-contracts.activate
employee-contracts.terminate
employee-contracts.cancel
employee-contracts.delete
employee-contracts.restore
employee-contracts.manage
```

- `hr-manager`: seluruh lifecycle kecuali force delete.
- `hr-officer`: view, create, update draft; activate/terminate hanya jika diberikan eksplisit.
- `hr-viewer`: view-only.
- Force delete tidak tersedia pada rilis awal karena kontrak adalah record historis.

### 3.4 Data contract

Rancangan tabel `hr_employee_contracts`:

| Field | Contract |
|---|---|
| `id` | Primary key internal |
| `employee_id` | Required FK ke employee aktif/arsip yang masih ada |
| `employment_type_id` | Required FK ke employment type |
| `contract_number` | Required, unique, immutable setelah activate |
| `start_date` | Required business date |
| `end_date` | Nullable; wajib untuk type tertentu; `>= start_date` |
| `probation_end_date` | Nullable; harus berada dalam periode kontrak |
| `signed_date` | Nullable; tidak boleh setelah activation date menurut policy final |
| `status` | `DRAFT`, `ACTIVE`, `ENDED`, `CANCELLED` |
| `ended_reason` | Required untuk terminate/cancel sesuai transition |
| `superseded_by_id` | Nullable self-reference ke kontrak pengganti |
| `notes` | Nullable, max 2000, internal HR |
| timestamps + `deleted_at` | Audit teknis dan soft delete |

Input DTO dan output page contract harus terpisah. Consumer lintas project tidak menerima model/table ini; HR Integration kemudian menerbitkan snapshot minimal seperti:

```json
{
  "schemaVersion": 1,
  "employeeId": 123,
  "contractId": 456,
  "employmentTypeCode": "FIXED_TERM",
  "validFrom": "2026-01-01",
  "validUntil": "2026-12-31",
  "capturedAt": "2026-01-01T00:00:00Z"
}
```

Snapshot tidak memuat notes atau attachment. Lihat [Payroll planning](../../../planning/payroll.md).

Implementasi v1 tersedia melalui `EmployeeContractSnapshotReader`, bukan route atau model publik. Caller wajib memberikan `effectiveDate` dan `capturedAt` agar hasil reproducible. Schema normatif berada di `Integration/Schemas/employee-contract-snapshot-v1.json`; keputusan versioning dan exposure field dijelaskan pada [ADR-002](decisions/002-versioned-snapshot-boundary.md).

## 4. Non-scope

- Penyimpanan file kontrak, versioning file, sharing, retention, OCR, dan e-signature.
- Compensation, allowance, deduction, tax, payroll calculation, atau bank account.
- Contract template/merge document dan mass generation.
- Automated reminder, notification, approval workflow, dan renewal wizard pada slice pertama.
- Perubahan department/position/supervisor; itu milik `EmployeeMovements`.
- Mengubah `Employees.employment_type_id` secara otomatis sebelum kebijakan sinkronisasi disepakati.

## 5. Project structure

```txt
app/Modules/HR/EmployeeContracts/
  DTO/  Database/Migrations/  Http/Controllers/  Http/Requests/
  Models/  Policies/  Providers/  Services/  Support/  Transactions/
  module.php  routes.php  permissions.php  navigation.php
resources/js/pages/hr/employee-contracts/
  index.tsx  types.ts  employee-contract-components/
tests/Feature/HREmployeeContractTest.php
docs/projects/hr/employee-contracts/
```

## 6. Route and command design

```txt
GET    /hr/employee-contracts
POST   /hr/employee-contracts
POST   /hr/employee-contracts/{contract}
POST   /hr/employee-contracts/{contract}/activate
POST   /hr/employee-contracts/{contract}/terminate
POST   /hr/employee-contracts/{contract}/cancel
DELETE /hr/employee-contracts/{contract}
PATCH  /hr/employee-contracts/{contract}/restore
```

Route names memakai `hr.employee-contracts.*`. Action lifecycle eksplisit karena bukan CRUD update biasa dan setiap transition memiliki authorization/validation berbeda.

Command masa depan:

```bash
php artisan hr:contracts-reconcile-statuses --date=2026-07-13 --dry-run
php artisan hr:contracts-expiring --within=30 --date=2026-07-13
```

Command mutasi wajib idempotent, default fail-closed, memiliki `--dry-run`, structured summary, dan tidak mengirim notification pada slice pertama.

## 7. Code style and boundaries

- Ikuti pola `Employees`: FormRequest → DTO → Service → Transaction → Model.
- Policy dan permission diperiksa server-side; frontend permission hanya presentation control.
- Enum/status memiliki satu sumber definisi backend dan union type frontend yang cocok.
- Seluruh overlap check dan write dijalankan dalam transaction; rancang perlindungan concurrency sebelum production.
- Selalu: test dulu, audit lifecycle, soft delete, pagination, module manifest, dan input normalization.
- Ask first: schema final, dependency baru, compensation field, attachment, approval, CI, atau event publik lintas project.
- Never: hard delete contract historis, akses model Payroll, menyimpan secret/credential, atau mengubah kontrak aktif lewat generic update.

## 8. Acceptance criteria

- HR berizin dapat membuat dan melihat draft contract melalui UI.
- Request tidak berizin menghasilkan `403` untuk setiap mutation.
- Nomor kontrak duplicate, tanggal terbalik, missing required end date, self-supersede, dan overlap ditolak dengan `422`.
- Hanya transition valid yang berhasil; transition invalid tidak mengubah database atau audit.
- Kontrak aktif pada tanggal boundary ditemukan secara inklusif.
- Archive tidak menghapus histori; restore tidak boleh menciptakan overlap.
- Page paginated, typed, buildable, dan tidak mengekspos notes ke consumer lintas project.
- Semua quality gates pada bagian berikut hijau.

## 9. Testing strategy

- Feature tests: auth, permission denial matrix, CRUD draft, lifecycle, filtering, soft delete/restore.
- Domain/service tests: inclusive boundaries, open-ended interval, overlap, supersede, invalid transitions.
- Database/concurrency test: dua activation paralel tidak menghasilkan dua kontrak aktif overlap; implementasi menyesuaikan database production.
- Frontend: form mapping, error state, filters, lifecycle buttons, serta characterization component penting.
- Contract test: manifest valid dan snapshot schema version stabil.

Commands:

```bash
php artisan test tests/Feature/HREmployeeContractTest.php
php artisan module:validate
vendor/bin/pint --test
npm run lint:check
npm run format:check
npm run typecheck
npm run test:frontend
npm run build
php artisan test
```

## 10. Open questions

- Apakah `contract_number` unik global atau per legal entity? Rilis awal mengasumsikan global karena legal entity belum dimodelkan.
- Apakah aktivasi wajib `signed_date`? Rilis awal: opsional.
- Apakah kontrak permanent tanpa end date boleh disupersede langsung atau harus terminate dahulu?
- Database production final MySQL/PostgreSQL perlu dikonfirmasi untuk strategi exclusion/locking overlap.
