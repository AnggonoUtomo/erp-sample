---
id: ARC-GUIDE-001
title: Acuan DDD-Lite Modular Monolith Laravel
document_type: architecture-guide
status: active
version: 1.1.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002]
---

# DDD-Lite Modular Monolith Laravel

> Interpretasi normative struktur folder berada pada ADR-0001. Jika contoh dalam dokumen ini berbeda, ADR-0001 berlaku: lokasi baku, tetapi hanya folder yang benar-benar dibutuhkan yang dibuat. Kondisi kode saat ini masih struktur lama dan tidak boleh disebut sudah bermigrasi.

**Dokumen Acuan Arsitektur dan Komunikasi Antar-Modul**  
**Versi:** 1.0  
**Status:** Baseline Arsitektur  
**Target:** Aplikasi Laravel modular monolith berbasis domain bisnis

---

## 1. Tujuan Dokumen

Dokumen ini menjadi acuan tetap dalam merancang dan mengimplementasikan aplikasi Laravel menggunakan pendekatan **DDD-lite Modular Monolith**.

Tujuan utamanya adalah:

1. Menetapkan satu pola arsitektur yang konsisten.
2. Mencegah perubahan struktur folder pada setiap proyek baru.
3. Menjelaskan batas tanggung jawab setiap modul.
4. Menyederhanakan komunikasi antar-modul.
5. Menentukan kapan menggunakan Contract, Event, Query Service, CQRS-lite, dan Shared Kernel.
6. Menjadi pedoman kerja bagi developer dan AI coding assistant.

Dokumen ini tidak menggunakan DDD penuh. Pola kompleks hanya diterapkan jika terdapat kebutuhan bisnis atau teknis yang nyata.

---

## 2. Prinsip Dasar

DDD-lite Modular Monolith berarti:

> Aplikasi tetap berada dalam satu project Laravel, satu repository, dan umumnya satu database, tetapi kode dipisahkan menjadi modul bisnis dengan batas tanggung jawab yang jelas.

Contoh modul pada aplikasi pesantren:

```text
app/
└── Modules/
    ├── Student/
    ├── Academic/
    ├── Finance/
    ├── HumanResource/
    ├── Document/
    └── Notification/
```

Setiap modul mewakili wilayah bisnis tertentu.

| Modul | Tanggung Jawab |
|---|---|
| Student | Data santri, wali, status santri, dan identitas santri |
| Academic | Kelas, mata pelajaran, jadwal, nilai, dan kenaikan kelas |
| Finance | Tagihan, pembayaran, kas, dan transaksi keuangan |
| HumanResource | Pegawai, jabatan, absensi, dan administrasi SDM |
| Document | Dokumen, arsip, surat, dan persetujuan dokumen |
| Notification | Email, WhatsApp, notifikasi aplikasi, dan template pesan |

### Prinsip utama

1. Modul dibentuk berdasarkan domain bisnis, bukan sekadar teknologi.
2. Setiap data memiliki satu modul pemilik.
3. Controller tidak menyimpan logika bisnis.
4. Komunikasi antar-modul harus melalui mekanisme yang terkontrol.
5. Pola kompleks tidak digunakan tanpa alasan konkret.
6. Struktur mengikuti kebutuhan bisnis, bukan bisnis dipaksa mengikuti struktur.

---

## 3. Baseline Arsitektur

Baseline yang digunakan:

```text
Laravel Modular Monolith
+
DDD-lite
+
Application Actions
+
Eloquent Models
+
Contract untuk komunikasi sinkron
+
Event untuk pemberitahuan kejadian
+
Query Service untuk pembacaan lintas modul
+
Shared Kernel yang sangat kecil
+
CQRS-lite bila diperlukan
```

Alur dasar dalam satu modul:

```text
HTTP Request
    ↓
Controller
    ↓
Application Action
    ↓
Domain Logic / Model / Contract
    ↓
Database

Setelah proses utama berhasil:
    ↓
Event
    ↓
Listener modul lain
```

---

## 4. Struktur Folder Baku

Struktur lengkap modul:

```text
app/
└── Modules/
    └── Student/
        ├── Application/
        │   ├── Actions/
        │   ├── DTOs/
        │   ├── Queries/
        │   ├── Services/
        │   └── Contracts/
        │
        ├── Domain/
        │   ├── Entities/
        │   ├── ValueObjects/
        │   ├── Events/
        │   ├── Services/
        │   ├── Exceptions/
        │   └── Contracts/
        │
        ├── Infrastructure/
        │   ├── Models/
        │   ├── Repositories/
        │   ├── Persistence/
        │   └── Providers/
        │
        ├── Presentation/
        │   ├── Http/
        │   │   ├── Controllers/
        │   │   ├── Requests/
        │   │   └── Resources/
        │   └── Routes/
        │
        ├── Database/
        │   ├── Migrations/
        │   ├── Factories/
        │   └── Seeders/
        │
        └── Tests/
            ├── Feature/
            └── Unit/
```

### Struktur minimal

Tidak semua folder harus dibuat sejak awal. Untuk modul sederhana, gunakan:

```text
Student/
├── Application/
│   └── Actions/
├── Infrastructure/
│   └── Models/
├── Presentation/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Requests/
│   └── Routes/
└── Tests/
```

Folder berikut dibuat hanya saat diperlukan:

- `DTOs`
- `Queries`
- `Contracts`
- `Domain/Entities`
- `Domain/ValueObjects`
- `Domain/Events`
- `Domain/Services`
- `Repositories`
- `Listeners`
- `Jobs`

### Aturan penting

> Jangan membuat folder kosong hanya untuk terlihat mengikuti DDD.

---

## 5. Tanggung Jawab Setiap Layer

### 5.1 Presentation

Presentation menangani interaksi dengan dunia luar.

Contoh:

- HTTP Controller
- Form Request
- API Resource
- Route
- Inertia response
- Console command tertentu

Presentation boleh:

- Menerima request.
- Menjalankan validasi melalui Form Request.
- Mengubah request menjadi DTO.
- Memanggil Application Action atau Query.
- Mengembalikan response.

Presentation tidak boleh:

- Menyimpan logika bisnis utama.
- Mengakses banyak model secara langsung untuk proses bisnis kompleks.
- Mengatur transaksi bisnis.
- Mengubah data milik modul lain.

---

### 5.2 Application

Application berisi use case aplikasi.

Contoh:

- `CreateStudentAction`
- `RecordPaymentAction`
- `PromoteStudentAction`
- `ApproveDocumentAction`
- `GetStudentDetailQuery`

Application bertanggung jawab untuk:

- Mengorkestrasi proses bisnis.
- Memanggil Domain Logic.
- Memanggil Contract modul lain.
- Mengelola transaksi database.
- Menghasilkan atau dispatch event.
- Mengembalikan hasil use case.

Application tidak boleh bergantung pada detail HTTP seperti `Request` atau `Response`.

---

### 5.3 Domain

Domain berisi aturan dan konsep bisnis yang penting.

Contoh:

- Entity
- Value Object
- Domain Event
- Domain Exception
- Domain Service
- Business invariant

Domain digunakan jika logika bisnis sudah cukup penting dan tidak pantas diletakkan langsung pada controller atau model sederhana.

DDD-lite tidak mewajibkan seluruh model menjadi pure domain entity.

---

### 5.4 Infrastructure

Infrastructure berisi detail teknis.

Contoh:

- Eloquent Model
- Implementasi repository
- Integrasi API eksternal
- Penyimpanan file
- Mailer
- Queue adapter
- Provider dan binding

Infrastructure dapat bergantung pada framework Laravel.

---

## 6. Alur Use Case Dalam Satu Modul

Contoh membuat santri baru:

```text
StoreStudentRequest
    ↓
StoreStudentController
    ↓
CreateStudentAction
    ↓
Student Model
    ↓
Database
```

### Controller

```php
final class StoreStudentController
{
    public function __invoke(
        StoreStudentRequest $request,
        CreateStudentAction $action,
    ): RedirectResponse {
        $student = $action->execute(
            CreateStudentData::fromRequest($request),
        );

        return redirect()->route('students.show', $student);
    }
}
```

### DTO

```php
final readonly class CreateStudentData
{
    public function __construct(
        public string $name,
        public string $studentNumber,
        public CarbonImmutable $birthDate,
    ) {}

    public static function fromRequest(StoreStudentRequest $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            studentNumber: $request->string('student_number')->toString(),
            birthDate: CarbonImmutable::parse($request->date('birth_date')),
        );
    }
}
```

### Action

```php
final class CreateStudentAction
{
    public function execute(CreateStudentData $data): Student
    {
        return Student::query()->create([
            'name' => $data->name,
            'student_number' => $data->studentNumber,
            'birth_date' => $data->birthDate,
        ]);
    }
}
```

Pola tersebut sudah cukup untuk modul sederhana.

Tidak perlu langsung menambahkan:

- Aggregate root
- Repository interface
- Command bus
- Event sourcing
- Query bus

---

## 7. Kepemilikan Data

Setiap tabel harus memiliki satu modul pemilik.

| Tabel | Modul Pemilik |
|---|---|
| `students` | Student |
| `guardians` | Student atau Guardian |
| `classes` | Academic |
| `subjects` | Academic |
| `invoices` | Finance |
| `payments` | Finance |
| `employees` | HumanResource |
| `documents` | Document |
| `notifications` | Notification |

### Aturan ownership

1. Hanya modul pemilik yang boleh mengubah data tersebut.
2. Modul lain boleh menyimpan ID referensi.
3. Modul lain tidak boleh mengubah Eloquent Model milik modul pemilik secara langsung.
4. Perubahan lintas modul dilakukan melalui Contract atau Event.
5. Query laporan boleh membaca lintas tabel, tetapi tidak mengambil alih kepemilikan data.

Contoh yang tidak diperbolehkan:

```php
// Finance mengubah status santri secara langsung.
Student::query()
    ->whereKey($studentId)
    ->update(['status' => 'inactive']);
```

Contoh yang diperbolehkan:

```php
$this->studentManagement->deactivateStudent($studentId);
```

atau, jika proses tidak membutuhkan hasil langsung:

```php
StudentDeactivationRequested::dispatch($studentId);
```

---

## 8. Komunikasi Antar-Modul

Baseline komunikasi antar-modul hanya menggunakan tiga mekanisme utama:

1. **Contract atau Application Service** untuk proses sinkron.
2. **Event** untuk pemberitahuan kejadian.
3. **Query atau Read Service** untuk pembacaan data.

Shared Kernel digunakan untuk konsep yang benar-benar umum dan stabil.

---

## 9. Contract untuk Komunikasi Sinkron

Gunakan Contract saat modul pemanggil membutuhkan hasil langsung dari modul lain.

Contoh:

> Finance ingin membuat tagihan dan harus memastikan santri masih aktif.

Alurnya:

```text
Finance
    ↓
StudentLookup Contract
    ↓
Implementasi pada Student Module
    ↓
Student Data
```

### Contract

```php
namespace App\Modules\Student\Application\Contracts;

interface StudentLookup
{
    public function findActiveStudent(int $studentId): ?StudentData;
}
```

### Implementasi

```php
final class EloquentStudentLookup implements StudentLookup
{
    public function findActiveStudent(int $studentId): ?StudentData
    {
        $student = Student::query()
            ->whereKey($studentId)
            ->where('status', 'active')
            ->first();

        return $student === null
            ? null
            : StudentData::fromModel($student);
    }
}
```

### Penggunaan dari Finance

```php
final class CreateInvoiceAction
{
    public function __construct(
        private StudentLookup $students,
    ) {}

    public function execute(int $studentId, Money $amount): Invoice
    {
        $student = $this->students->findActiveStudent($studentId);

        if ($student === null) {
            throw new StudentNotEligibleForInvoice();
        }

        return Invoice::query()->create([
            'student_id' => $student->id,
            'amount' => $amount->value(),
        ]);
    }
}
```

### Gunakan Contract ketika

- Hasil dibutuhkan saat itu juga.
- Proses tidak dapat dilanjutkan tanpa jawaban.
- Validasi harus terjadi dalam request yang sama.
- Kegagalan modul tujuan harus menggagalkan proses utama.
- Operasi membutuhkan respons sukses atau gagal.

### Prinsip

> Butuh jawaban sekarang → gunakan Contract.

---

## 10. Event untuk Pemberitahuan Kejadian

Gunakan Event ketika proses utama sudah selesai dan modul lain hanya perlu mengetahui kejadian tersebut.

Contoh:

```text
Student berhasil dibuat
    ↓
StudentRegistered Event
    ├── Finance menyiapkan akun tagihan
    ├── Document membuat folder dokumen
    └── Notification mengirim pesan sambutan
```

### Event

```php
final readonly class StudentRegistered
{
    public function __construct(
        public int $studentId,
        public string $studentNumber,
    ) {}
}
```

### Dispatch Event

```php
final class CreateStudentAction
{
    public function execute(CreateStudentData $data): Student
    {
        $student = Student::query()->create([
            'name' => $data->name,
            'student_number' => $data->studentNumber,
        ]);

        StudentRegistered::dispatch(
            studentId: $student->id,
            studentNumber: $student->student_number,
        );

        return $student;
    }
}
```

### Listener

```php
final class PrepareStudentBillingAccount
{
    public function handle(StudentRegistered $event): void
    {
        BillingAccount::query()->firstOrCreate([
            'student_id' => $event->studentId,
        ]);
    }
}
```

### Gunakan Event ketika

- Modul asal tidak membutuhkan hasil langsung.
- Proses tambahan dapat dijalankan setelah proses utama.
- Satu kejadian dapat ditanggapi beberapa modul.
- Proses dapat dipindahkan ke queue.
- Kegagalan listener tidak harus menggagalkan proses utama.

### Prinsip

> Hanya memberi tahu bahwa sesuatu telah terjadi → gunakan Event.

---

## 11. Domain Event dan Laravel Event

Secara konsep:

- **Domain Event** adalah kejadian bisnis.
- **Laravel Event** adalah mekanisme teknis untuk menyebarkan kejadian tersebut.

Contoh nama event bisnis:

```text
StudentRegistered
InvoiceIssued
InvoicePaid
EmployeeTerminated
DocumentApproved
ClassPromotionCompleted
```

Untuk baseline proyek, Laravel Event boleh langsung digunakan sebagai implementasi Domain Event.

Tidak perlu langsung membuat:

```text
Domain Event
→ Integration Event
→ Event Mapper
→ Event Bus
→ Message Broker
```

Pemisahan tersebut hanya diperlukan jika aplikasi sudah berkomunikasi dengan service eksternal atau arsitektur terdistribusi.

---

## 12. Event dan Transaksi Database

Event sebaiknya dipublikasikan setelah data utama berhasil disimpan.

Contoh:

```php
final class RecordPaymentAction
{
    public function execute(int $invoiceId, Money $amount): Payment
    {
        $result = DB::transaction(function () use ($invoiceId, $amount) {
            $invoice = Invoice::query()
                ->lockForUpdate()
                ->findOrFail($invoiceId);

            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'amount' => $amount->value(),
            ]);

            $invoice->recordPayment($amount);

            return [$invoice, $payment];
        });

        [$invoice, $payment] = $result;

        InvoicePaid::dispatch(
            invoiceId: $invoice->id,
            paymentId: $payment->id,
            studentId: $invoice->student_id,
        );

        return $payment;
    }
}
```

Laravel juga menyediakan mekanisme event atau listener setelah commit. Gunakan jika listener tidak boleh berjalan sebelum transaksi berhasil dikomit.

---

## 13. Query dan Read Service

Gunakan Query atau Read Service ketika modul hanya ingin membaca data.

Contoh kebutuhan:

- Finance ingin menampilkan nama santri.
- Dashboard ingin menampilkan total tunggakan.
- Laporan ingin menggabungkan data santri, kelas, dan pembayaran.

### Read Service

```php
interface StudentReadService
{
    public function getStudentSummary(int $studentId): StudentSummaryData;
}
```

### Query laporan lintas modul

```php
final class StudentBillingReportQuery
{
    public function execute(): Collection
    {
        return DB::table('students')
            ->join('invoices', 'students.id', '=', 'invoices.student_id')
            ->select([
                'students.id',
                'students.name',
                DB::raw('SUM(invoices.remaining_amount) AS outstanding'),
            ])
            ->groupBy('students.id', 'students.name')
            ->get();
    }
}
```

### Join lintas tabel diperbolehkan jika

- Operasinya hanya membaca.
- Digunakan untuk laporan, dashboard, atau read model.
- Tidak mengubah data modul lain.
- Tidak menyembunyikan aturan bisnis penting.
- Diletakkan pada Query atau Reporting layer yang jelas.

### Prinsip

> Hanya membaca data → gunakan Query atau Read Service.

---

## 14. CQRS-Lite

CQRS memisahkan operasi menjadi:

- **Command** untuk mengubah data.
- **Query** untuk membaca data.

Pada DDD-lite, CQRS diterapkan secara sederhana melalui Actions dan Queries.

```text
Application/
├── Actions/
│   ├── CreateStudentAction.php
│   ├── UpdateStudentAction.php
│   └── DeactivateStudentAction.php
└── Queries/
    ├── GetStudentDetailQuery.php
    └── ListStudentsQuery.php
```

Tidak perlu langsung menggunakan:

- Command Bus
- Query Bus
- Handler Registry
- Separate read database
- Separate write database
- Event sourcing

### Aturan

1. Gunakan `Action` untuk operasi perubahan data.
2. Gunakan `Query` untuk pembacaan yang kompleks.
3. Query sederhana boleh tetap langsung pada controller atau service yang sesuai.
4. CQRS penuh hanya digunakan jika kompleksitas aplikasi membutuhkannya.

---

## 15. Shared Kernel

Shared Kernel adalah tempat konsep yang benar-benar umum, stabil, dan digunakan beberapa modul.

Contoh:

```text
app/
└── Shared/
    ├── Domain/
    │   ├── Money.php
    │   ├── DateRange.php
    │   ├── PhoneNumber.php
    │   └── EmailAddress.php
    ├── Application/
    │   └── PaginationData.php
    └── Infrastructure/
        ├── Clock.php
        └── UuidGenerator.php
```

### Yang layak masuk Shared Kernel

- `Money`
- `DateRange`
- `PhoneNumber`
- `EmailAddress`
- `PaginationData`
- `Clock`
- `UuidGenerator`
- Base exception yang sangat umum

### Yang tidak layak masuk Shared Kernel

```text
Shared/
├── StudentService.php
├── InvoiceService.php
├── EmployeeHelper.php
├── AcademicUtility.php
└── CommonRepository.php
```

### Aturan

1. Jika hanya digunakan satu modul, tetap simpan di modul tersebut.
2. Jangan memindahkan kode hanya karena ada dua pemakaian kecil.
3. Shared Kernel harus kecil dan stabil.
4. Shared Kernel tidak boleh menjadi tempat sampah kode bersama.

---

## 16. Value Object

Value Object digunakan untuk konsep yang memiliki aturan, validasi, atau perilaku sendiri.

Contoh:

- Money
- PhoneNumber
- EmailAddress
- StudentNumber
- DateRange
- Percentage

Contoh sederhana:

```php
final readonly class Money
{
    public function __construct(
        private int $amount,
        private string $currency = 'IDR',
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount tidak boleh negatif.');
        }
    }

    public function value(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }
}
```

Jangan membuat Value Object untuk setiap field tanpa manfaat nyata.

---

## 17. Repository

Repository tidak wajib untuk semua model.

Gunakan Eloquent secara langsung jika:

- Query sederhana.
- Modul belum kompleks.
- Tidak membutuhkan pergantian persistence.
- Tidak ada domain entity terpisah.

Contoh yang cukup:

```php
$student = Student::query()->findOrFail($studentId);
```

Gunakan Repository jika:

- Aggregate memiliki proses persistence kompleks.
- Query penyimpanan berulang dan penting.
- Domain tidak ingin bergantung pada Eloquent.
- Terdapat kebutuhan pengujian atau implementasi penyimpanan alternatif.

Hindari generic repository seperti:

```php
interface BaseRepository
{
    public function all();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
```

Repository harus menggambarkan kebutuhan bisnis, bukan sekadar membungkus semua method Eloquent.

---

## 18. Domain Service

Domain Service digunakan jika aturan bisnis:

- Tidak cocok dimiliki satu Entity.
- Melibatkan beberapa konsep domain.
- Tidak sekadar orkestrasi teknis.

Contoh:

```php
final class TuitionCalculationService
{
    public function calculate(
        StudentCategory $category,
        AcademicPeriod $period,
        Collection $discounts,
    ): Money {
        // Aturan bisnis perhitungan biaya.
    }
}
```

Application Service dan Domain Service berbeda:

| Jenis | Fungsi |
|---|---|
| Application Service / Action | Mengorkestrasi use case |
| Domain Service | Menjalankan aturan bisnis domain |

---

## 19. Decision Tree Komunikasi Antar-Modul

Gunakan alur keputusan berikut.

### Pertanyaan 1

Apakah modul pemanggil membutuhkan hasil langsung?

- Ya → gunakan **Contract/Application Service**.
- Tidak → lanjut ke pertanyaan 2.

### Pertanyaan 2

Apakah modul lain hanya perlu mengetahui sebuah kejadian?

- Ya → gunakan **Event**.
- Tidak → lanjut ke pertanyaan 3.

### Pertanyaan 3

Apakah kebutuhan hanya membaca atau menampilkan data?

- Ya → gunakan **Query/Read Service**.
- Tidak → evaluasi ulang batas modul dan ownership.

### Pertanyaan 4

Apakah konsep benar-benar umum, stabil, dan digunakan banyak modul?

- Ya → pertimbangkan **Shared Kernel**.
- Tidak → tetap simpan dalam modul pemilik.

### Ringkasan

```text
Butuh jawaban langsung   → Contract
Memberi tahu kejadian    → Event
Hanya membaca data       → Query / Read Service
Konsep umum dan stabil   → Shared Kernel
Read/write mulai kompleks → CQRS-lite
```

---

## 20. Contoh Lengkap: Pembayaran Tagihan

### Alur utama

```text
User membayar tagihan
    ↓
PaymentController
    ↓
RecordPaymentAction
    ↓
Invoice dan Payment diperbarui
    ↓
InvoicePaid Event
    ├── Notification mengirim bukti pembayaran
    ├── Accounting membuat jurnal
    └── Student Portal memperbarui status tampilan
```

### Sebelum pembayaran

Finance perlu memastikan invoice valid dan masih dapat dibayar. Proses ini terjadi langsung di modul Finance.

Jika Finance membutuhkan informasi santri aktif, Finance memanggil Contract milik Student.

### Setelah pembayaran

Finance tidak perlu memanggil Notification, Accounting, dan Portal satu per satu.

Finance cukup menerbitkan:

```php
InvoicePaid::dispatch(
    invoiceId: $invoice->id,
    paymentId: $payment->id,
    studentId: $invoice->student_id,
);
```

Masing-masing modul menangani listener sendiri.

### Kesimpulan pola

- Contract digunakan sebelum proses utama ketika jawaban dibutuhkan.
- Event digunakan setelah proses utama ketika modul lain hanya perlu bereaksi.

---

## 21. Aturan Dependency

Arah dependency konseptual:

```text
Presentation
    ↓
Application
    ↓
Domain
    ↑
Infrastructure
```

Dalam implementasi Laravel:

- Controller memanggil Action atau Query.
- Action boleh memakai Contract.
- Implementasi Contract berada di Infrastructure atau modul penyedia.
- Domain tidak bergantung pada Controller, Request, Inertia, atau View.
- Modul lain tidak memanggil Controller modul tujuan.
- Modul lain tidak mengubah Model milik modul tujuan secara langsung.

### Dilarang

```php
$controller = new StudentController();
$student = $controller->show($id);
```

```php
Student::query()->whereKey($id)->update([...]);
```

### Diperbolehkan

```php
$student = $this->studentLookup->findActiveStudent($id);
```

```php
StudentStatusChanged::dispatch($id, $status);
```

---

## 22. Transaksi Lintas Modul

Dalam modular monolith dengan satu database, transaksi lintas beberapa tabel masih memungkinkan. Namun, gunakan secara hati-hati.

### Gunakan transaksi sinkron jika

- Seluruh perubahan harus berhasil atau gagal bersama.
- Proses berlangsung cepat.
- Kegagalan sebagian akan merusak konsistensi bisnis.

### Gunakan Event jika

- Proses tambahan dapat dipisahkan.
- Tidak harus selesai pada request yang sama.
- Proses dapat diulang.
- Proses cocok dijalankan melalui queue.

Jangan memaksakan event untuk proses yang sebenarnya harus atomik.

---

## 23. Idempotency pada Listener

Listener yang berjalan melalui queue dapat dieksekusi ulang. Karena itu, listener penting harus idempotent.

Contoh:

```php
BillingAccount::query()->firstOrCreate([
    'student_id' => $event->studentId,
]);
```

Untuk integrasi pembayaran, gunakan identifier unik:

```php
Payment::query()->firstOrCreate(
    ['external_reference' => $reference],
    $paymentData,
);
```

Tujuannya agar eksekusi ulang tidak menghasilkan data ganda.

---

## 24. Penamaan

Gunakan nama yang menggambarkan bisnis.

### Action

```text
CreateStudentAction
RegisterStudentAction
RecordPaymentAction
ApproveDocumentAction
PromoteStudentAction
```

### Query

```text
GetStudentDetailQuery
ListOutstandingInvoicesQuery
GetAcademicReportQuery
```

### Event

Event menggunakan bentuk lampau karena menyatakan sesuatu yang telah terjadi.

```text
StudentRegistered
InvoicePaid
DocumentApproved
EmployeeTerminated
```

### Contract

```text
StudentLookup
StudentManagement
InvoiceReader
NotificationSender
```

Hindari nama terlalu umum:

```text
DataService
CommonService
Helper
Manager
Utility
BaseRepository
```

---

## 25. Pola yang Tidak Menjadi Default

Pola berikut tidak otomatis digunakan:

- Full CQRS
- Event sourcing
- Aggregate Repository untuk semua Entity
- Separate read database
- Separate write database
- Message broker
- Saga
- Process Manager
- Anti-Corruption Layer untuk semua modul
- Shared Kernel besar
- Generic Repository
- Generic Service
- Command Bus
- Query Bus
- Hexagonal Architecture penuh
- Clean Architecture dengan terlalu banyak interface

Pola tersebut hanya digunakan jika terdapat masalah konkret yang memerlukannya.

---

## 26. Checklist Pembuatan Modul Baru

### A. Definisi modul

- [ ] Nama modul mewakili wilayah bisnis.
- [ ] Tanggung jawab modul dapat dijelaskan dalam satu atau dua kalimat.
- [ ] Data utama modul telah diidentifikasi.
- [ ] Batas dengan modul lain telah ditentukan.

### B. Struktur awal

- [ ] Membuat folder minimal yang dibutuhkan.
- [ ] Tidak membuat folder kosong tanpa kebutuhan.
- [ ] Controller berada pada Presentation.
- [ ] Use case berada pada Application/Actions.
- [ ] Model Eloquent berada pada Infrastructure/Models.

### C. Ownership

- [ ] Setiap tabel memiliki satu modul pemilik.
- [ ] Modul lain tidak mengubah model tersebut secara langsung.
- [ ] Referensi lintas modul menggunakan ID atau DTO yang jelas.

### D. Komunikasi

- [ ] Contract digunakan jika hasil dibutuhkan langsung.
- [ ] Event digunakan jika hanya memberi tahu kejadian.
- [ ] Query digunakan untuk pembacaan.
- [ ] Shared Kernel hanya untuk konsep umum dan stabil.

### E. Kualitas

- [ ] Controller tetap tipis.
- [ ] Action memiliki satu use case utama.
- [ ] Transaksi ditempatkan di Application Action.
- [ ] Event memiliki nama bisnis dalam bentuk lampau.
- [ ] Listener queue bersifat idempotent.
- [ ] Test tersedia untuk aturan bisnis penting.

---

## 27. Checklist Review Pull Request

### Arsitektur

- [ ] Perubahan ditempatkan pada modul yang benar.
- [ ] Tidak ada pelanggaran ownership data.
- [ ] Tidak ada pemanggilan Controller lintas modul.
- [ ] Tidak ada update langsung terhadap Model modul lain.
- [ ] Contract, Event, atau Query dipilih dengan alasan yang tepat.

### Application

- [ ] Controller hanya mengurus HTTP.
- [ ] Logika use case berada pada Action.
- [ ] Transaksi database berada pada level yang benar.
- [ ] DTO digunakan ketika data input mulai kompleks.

### Domain

- [ ] Aturan bisnis penting tidak tersebar di banyak tempat.
- [ ] Exception menggambarkan kegagalan bisnis.
- [ ] Value Object hanya dibuat jika memiliki manfaat nyata.

### Event

- [ ] Event menyatakan kejadian yang telah terjadi.
- [ ] Payload event cukup tetapi tidak berlebihan.
- [ ] Listener tidak menyebabkan duplikasi data.
- [ ] Listener queue memiliki strategi retry yang aman.

### Testing

- [ ] Happy path diuji.
- [ ] Validation failure diuji.
- [ ] Business rule failure diuji.
- [ ] Event dispatch diuji jika penting.
- [ ] Listener penting diuji secara terpisah.

---

## 28. Pedoman untuk AI Coding Assistant

Gunakan instruksi berikut saat meminta AI mengembangkan modul.

```text
Gunakan baseline DDD-lite Modular Monolith Laravel berikut:

1. Modul dibentuk berdasarkan domain bisnis.
2. Gunakan struktur folder minimal; jangan membuat folder kosong.
3. Controller hanya menangani HTTP.
4. Use case ditempatkan pada Application/Actions.
5. Eloquent Model ditempatkan pada Infrastructure/Models.
6. Gunakan Contract untuk komunikasi sinkron yang membutuhkan hasil langsung.
7. Gunakan Event untuk pemberitahuan kejadian yang dapat dipisahkan.
8. Gunakan Query atau Read Service untuk pembacaan lintas modul.
9. Setiap tabel memiliki satu modul pemilik.
10. Modul lain tidak boleh mengubah Model pemilik secara langsung.
11. Shared Kernel harus kecil dan hanya berisi konsep umum yang stabil.
12. Jangan menerapkan full CQRS, Event Sourcing, Saga, Generic Repository,
    atau pola kompleks lain tanpa kebutuhan konkret.
13. Gunakan transaksi pada Application Action bila perubahan harus atomik.
14. Event penting diterbitkan setelah transaksi berhasil.
15. Listener queue harus idempotent.
16. Sertakan test untuk aturan bisnis utama.
```

### Instruksi tambahan saat membuat fitur

```text
Sebelum menulis kode:

1. Tentukan modul pemilik use case.
2. Tentukan tabel yang dimiliki modul.
3. Identifikasi komunikasi lintas modul.
4. Pilih Contract, Event, atau Query menggunakan decision tree.
5. Jelaskan alasan pemilihan pola.
6. Tampilkan struktur file yang benar-benar akan dibuat.
7. Implementasikan secara incremental.
8. Jangan mengubah baseline arsitektur tanpa alasan dan persetujuan eksplisit.
```

---

## 29. Anti-Pattern yang Harus Dihindari

### Fat Controller

```php
public function store(Request $request)
{
    // Validasi
    // Create santri
    // Create invoice
    // Kirim WhatsApp
    // Buat dokumen
    // Catat audit
    // Return response
}
```

Solusi: pindahkan orkestrasi ke Action dan gunakan Event untuk reaksi terpisah.

### Direct Cross-Module Model Access

```php
Student::query()->update(...);
```

dari dalam Finance.

Solusi: gunakan Contract milik Student.

### Shared Folder sebagai Tempat Sampah

```text
Shared/Helpers/CommonHelper.php
```

Solusi: letakkan kode pada modul pemilik dan pindahkan ke Shared hanya jika benar-benar umum.

### Event untuk Semua Hal

Event tidak cocok jika proses harus mendapatkan jawaban langsung atau harus atomik.

### Interface untuk Semua Class

Tidak semua Action, Service, atau Repository membutuhkan interface.

Interface digunakan pada boundary, variasi implementasi, atau dependency lintas modul yang stabil.

---

## 30. Kesimpulan

Baseline arsitektur yang dibekukan adalah:

```text
Controller
    ↓
Application Action
    ↓
Domain Logic / Eloquent Model / Contract
    ↓
Database

Setelah proses utama berhasil:
    ↓
Domain Event
    ↓
Listener modul lain
```

Empat aturan terpenting:

1. **Satu modul mewakili satu wilayah bisnis.**
2. **Controller hanya menerima dan mengembalikan HTTP.**
3. **Butuh jawaban langsung menggunakan Contract.**
4. **Hanya memberi tahu kejadian menggunakan Event.**

Query, CQRS-lite, Shared Kernel, Value Object, Repository, dan Domain Service adalah alat tambahan. Semua pola tersebut hanya digunakan ketika memberi manfaat nyata.

Arsitektur ini harus menjadi baseline yang sama untuk proyek Laravel berikutnya. Perubahan pola hanya dilakukan jika terdapat kebutuhan konkret, disertai alasan teknis atau bisnis yang jelas.
