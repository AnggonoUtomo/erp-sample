# Specification: Employee Documents

## 1. Objective

Membangun module `EmployeeDocuments` agar HR officer/manager dapat mencatat dokumen yang wajib atau relevan bagi employee, memantau masa berlaku, dan memverifikasi metadata tanpa menciptakan storage file kedua di project HR.

Keberhasilan berarti HR dapat menjawab: dokumen apa yang dimiliki employee, mana yang belum diverifikasi, mana yang akan/sudah kedaluwarsa, dan file DMS mana yang terkait—tanpa membaca tabel atau model internal Document Management.

## 2. Target user dan asumsi

1. Pengguna utama adalah `hr-officer`, `hr-manager`, dan auditor HR read-only.
2. `Employees` adalah sumber employee; tipe dokumen HR berasal dari `HRReferenceData` category simbolik `EMPLOYEE_DOCUMENT_TYPE` dengan code database `employee-document-type`.
3. Vertical slice pertama tidak menerima upload; `document_reference` nullable sampai Document Management tersedia.
4. Tanggal adalah business date `YYYY-MM-DD`; query expiry selalu menerima tanggal acuan eksplisit.
5. Nomor dokumen dapat mengandung PII dan tidak boleh masuk event, log description, atau snapshot umum secara utuh.
6. Satu dokumen HR dapat menunjuk satu logical document DMS; version aktif dikelola DMS.

## 3. Requirements

### 3.1 Metadata dan lifecycle

- Membuat dan melihat metadata dokumen employee dengan pagination dan filter.
- Menyimpan employee, tipe dokumen, nomor opsional, issuer opsional, issued date, expires date opsional, notes internal, dan reference DMS opsional.
- Tipe dokumen menentukan `requires_expiry`; tanggal kedaluwarsa wajib bila aturan tersebut aktif.
- Menormalisasi nomor dokumen untuk duplicate check tanpa mengubah tampilan yang dibutuhkan HR.
- Mengarsipkan dan memulihkan record metadata dengan soft delete; tidak menyediakan force delete pada rilis awal.
- Archive melepaskan uniqueness claim aktif tanpa menghapus encrypted history; restore menghitung ulang claim dan memvalidasi duplicate, employee, serta type aktif.
- Mencatat audit create, update draft metadata, verify, reject, archive, restore, attach, dan detach reference.

### 3.2 Verification state

```txt
PENDING ──verify──> VERIFIED
   │                   │
   └──reject────────> REJECTED
REJECTED ──resubmit──> PENDING
VERIFIED ──resubmit──> PENDING
```

- `PENDING`: metadata/file belum selesai diperiksa.
- `VERIFIED`: pemeriksa menyatakan dokumen sesuai pada waktu tertentu.
- `REJECTED`: tidak sesuai; reason wajib.
- Verify/reject menyimpan actor, timestamp, dan reason/catatan tanpa mengubah file DMS.
- Penggantian reference atau field identitas penting mengembalikan verification ke `PENDING` secara atomic.
- Field material adalah employee, document type, document number, issuer, issued/expiry date, dan DMS reference/version; perubahan notes saja tidak mereset verification.

### 3.3 Expiry state

Expiry tidak disimpan sebagai lifecycle status. State dihitung untuk tanggal `D`:

```txt
expires_at IS NULL                  => NOT_APPLICABLE
expires_at < D                      => EXPIRED
D <= expires_at <= D + warningDays  => EXPIRING
expires_at > D + warningDays        => VALID
```

Default `warningDays = 30`, tetapi query/command harus menerima nilai eksplisit agar reproducible. Verification dan expiry independen: dokumen dapat `VERIFIED + EXPIRED`.

List memakai query contract `as_of=YYYY-MM-DD`, `warning_days=0..3650`, dan optional `expiry_state`. Response selalu mengembalikan tanggal acuan serta warning window yang telah di-resolve, sehingga label state dan pagination tetap reproducible. Nilai `as_of` valid tidak boleh diganti dengan clock server.

### 3.4 Authorization

```txt
employee-documents.view
employee-documents.create
employee-documents.update
employee-documents.verify
employee-documents.archive
employee-documents.restore
employee-documents.attach
employee-documents.manage
```

- `hr-manager`: seluruh lifecycle metadata.
- `hr-officer`: view, create, update; verify/attach hanya jika diberikan eksplisit.
- `hr-viewer`: view metadata non-sensitif.
- Permission melihat metadata HR tidak otomatis memberi permission download file DMS.
- DMS tetap melakukan authorization sendiri pada setiap preview/download.

## 4. Data contract

Rancangan tabel `hr_employee_documents`:

| Field | Contract |
|---|---|
| `id` | Primary key internal HR |
| `employee_id` | Required FK internal ke Employees |
| `document_type_id` | Required FK internal ke HR Reference Data |
| `document_number` | Nullable, encrypted/masked policy diputuskan sebelum coding |
| `document_number_fingerprint` | Nullable keyed hash untuk duplicate lookup tanpa plaintext comparison |
| `document_number_uniqueness_key` | Nullable keyed hash unik berisi scope context untuk race-safe duplicate guard |
| `issuer` | Nullable, max 255 |
| `issued_at` | Nullable business date |
| `expires_at` | Nullable; required menurut type metadata |
| `verification_status` | `PENDING`, `VERIFIED`, `REJECTED` |
| `verified_by`, `verified_at` | Nullable; wajib bersama saat verified/rejected |
| `verification_reason` | Required saat rejected; nullable selainnya |
| `document_reference` | Nullable opaque ID dari DMS, bukan path/URL/media row ID |
| `document_reference_version` | Contract version reference, bukan versi blob yang dikelola HR |
| `notes` | Nullable internal HR, max 2000 |
| timestamps + `deleted_at` | Audit teknis dan soft delete |

Tidak ada kolom `disk`, `path`, `filename`, `mime_type`, `size`, `checksum`, `media_id`, `signed_url`, atau binary content di tabel HR.

## 5. Boundary Document Management

Contract aplikasi yang diharapkan, bukan direct model access:

```txt
DocumentReferenceReader
  exists(reference): bool
  describe(reference, actor): DocumentDescriptor

EmployeeDocumentAttachmentGateway
  createFor(attachmentRequest): DocumentReference
  authorizeAccess(reference, actor, action): AccessDecision
```

`ownerContext` minimal dan versioned:

```json
{
  "schemaVersion": 1,
  "domain": "HR",
  "aggregateType": "EmployeeDocument",
  "aggregateId": "817"
}
```

HR hanya menyimpan reference hasil DMS. DMS tidak memperoleh hak membaca seluruh profile employee. Nama employee, nomor identitas, dan notes tidak dikirim kecuali contract baru disetujui secara eksplisit.

Failure semantics:

- DMS unavailable: create/update metadata tetap dapat dilakukan; attach/download gagal tertutup tanpa partial reference.
- Reference tidak ditemukan/diarsipkan: HR menampilkan `UNAVAILABLE`, tidak menghapus metadata otomatis.
- Akses ditolak DMS: HR meneruskan denial; tidak membuat URL storage sendiri.
- Archive metadata HR tidak otomatis menghapus document DMS; retention/release ownership adalah use case eksplisit DMS.

Contract v1 yang disetujui tersedia sebagai `DocumentReferenceReader`, `EmployeeDocumentOwnerContextV1`, `DocumentReferenceV1`, dan `DocumentReferenceDescriptorV1`. Descriptor hanya memuat `schemaVersion`, opaque `reference`, dan state `AVAILABLE|MISSING|ARCHIVED|UNAVAILABLE|DENIED`. Schema normatif owner context berada di `Integration/Schemas/employee-document-owner-context-v1.json`; fake adapter hanya untuk contract test dan tidak menjadi production binding.

## 6. Non-scope

- Blob/file storage, Spatie media collection baru pada model EmployeeDocument, filesystem path, atau signed URL generation.
- Versioning file, MIME/magic-byte validation, checksum, malware scan, preview, OCR, sharing, retention, legal hold, dan permanent deletion.
- Approval workflow multi-step, bulk import, notification delivery, e-signature, dan document request portal.
- Contract lifecycle, movement, employee identity master, payroll attachment, atau public API.
- Sinkronisasi dua arah melalui direct database FK antara HR dan DMS.

## 7. Project structure

```txt
app/Modules/HR/EmployeeDocuments/
  DTO/  Database/Migrations/  Http/Controllers/  Http/Requests/
  Models/  Policies/  Providers/  Services/  Support/  Transactions/
  Integration/Contracts/  Integration/DTO/
  module.php  routes.php  permissions.php  navigation.php
resources/js/pages/hr/employee-documents/
  index.tsx  types.ts  employee-document-components/
tests/Feature/HREmployeeDocumentTest.php
tests/Feature/HREmployeeDocumentAuthorizationTest.php
docs/projects/hr/employee-documents/
```

## 8. Route dan command design

```txt
GET    /hr/employee-documents
POST   /hr/employee-documents
PATCH  /hr/employee-documents/{employeeDocument}
POST   /hr/employee-documents/{employeeDocument}/verify
POST   /hr/employee-documents/{employeeDocument}/reject
POST   /hr/employee-documents/{employeeDocument}/resubmit
POST   /hr/employee-documents/{employeeDocument}/attachment
DELETE /hr/employee-documents/{employeeDocument}/attachment
DELETE /hr/employee-documents/{employeeDocument}
PATCH  /hr/employee-documents/{employeeDocument}/restore
```

Route names memakai `hr.employee-documents.*`. Attachment routes baru diaktifkan setelah DMS gateway tersedia.

Command read-only yang direncanakan:

```bash
php artisan hr:documents-expiring --date=2026-07-13 --within=30
```

Command wajib deterministik, read-only, tidak mengirim notification, dan tidak mengubah verification status.

`--within` menerima integer 0–3650. Match dan empty result memakai exit code `0`; input invalid memakai exit code non-zero. Output hanya menampilkan metadata ID, employee, document type, dan expiry date—bukan nomor dokumen.

## 9. Boundaries dan code style

- Ikuti `FormRequest → DTO → Service → Transaction → Model`.
- Policy server-side wajib untuk setiap mutation; frontend permission hanya menyembunyikan kontrol.
- Semua state transition dan audit dilakukan dalam transaction.
- Selalu: paginate, normalize input, mask sensitive output, soft delete, audit actor, dan test denial matrix.
- Ask first: schema database, encryption key strategy, DMS interface final, event lintas project, reminder/queue, dan perubahan retention.
- Never: menyimpan file/path/URL di HR, direct import model DMS, memasukkan nomor dokumen plaintext ke audit/event, atau menghapus blob saat metadata diarsipkan.

## 10. Acceptance criteria

- HR berizin dapat membuat dan melihat metadata dokumen tanpa upload file.
- Type yang mewajibkan expiry menolak input tanpa `expires_at`.
- Verification transition valid tercatat dengan actor/timestamp; transition/reason invalid ditolak tanpa partial write.
- Expiry query menghasilkan `NOT_APPLICABLE`, `VALID`, `EXPIRING`, atau `EXPIRED` secara deterministik.
- Update field identitas/reference mengembalikan status ke `PENDING`.
- Archive/restore menjaga histori dan tidak menghapus file DMS.
- Semua mutation tanpa permission menghasilkan `403`.
- Tidak ada storage column/media collection/file write di module HR.
- Attachment hanya melalui gateway dan authorization DMS.
- Quality gates backend/frontend/module seluruhnya hijau.

## 11. Testing strategy

- Feature: create/list/filter/update, required expiry, duplicate policy, archive/restore.
- Lifecycle: verify, reject dengan reason, resubmit, reset verification setelah perubahan material.
- Authorization: global mutation route × permission denial matrix.
- Domain: expiry boundary pada before/on/after date dan warning window.
- Security: masked output, nomor tidak muncul di audit, IDOR attach/download ditolak oleh dua policy boundary.
- Contract: fake DMS gateway, unavailable/missing/denied reference, no partial attach, owner context schema v1.
- Architecture: larangan import model DMS dan larangan media/storage fields pada HR.

Commands:

```bash
php artisan test tests/Feature/HREmployeeDocumentTest.php
php artisan module:validate
vendor/bin/pint --test
npm run lint:check
npm run format:check
npm run typecheck
npm run test:frontend
npm run build
php artisan test
```

## 12. Open questions

- Apakah nomor dokumen harus disimpan encrypted dan dicari lewat keyed fingerprint? Draft merekomendasikan ya untuk KTP/NPWP/passport.
- Apakah uniqueness nomor berlaku global per type atau per employee? Draft mengasumsikan per type secara global untuk identity document, configurable per type.
- Siapa yang boleh verify: HR manager saja atau role verifier terpisah?
- Saat metadata HR diarsipkan, apakah ownership DMS dipertahankan tanpa batas atau dilepas setelah retention policy tertentu?
- Apakah replacement file selalu reset verification? Draft: ya.
