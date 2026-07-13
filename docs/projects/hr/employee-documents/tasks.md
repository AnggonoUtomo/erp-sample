# Tasks: Employee Documents

Semua task belum dikerjakan. Implementasi harus berurutan dan berhenti di setiap checkpoint untuk review.

## Task 01 — Document type contract ✅

**Tujuan:** mendefinisikan tipe dokumen HR dan aturan apakah expiry/nomor wajib tanpa membuat master type kedua di DMS.

**Files:** HR Reference Data seeder/category contract, Employee Documents support type, tests, specification jika keputusan berubah.

**Acceptance criteria:**

- [x] Category simbolik `EMPLOYEE_DOCUMENT_TYPE` memakai code database stabil `employee-document-type` dan metadata `requires_expiry`, `requires_number`, serta `number_unique_scope` tervalidasi.
- [x] Type DMS/category storage tidak dipakai sebagai pengganti business type HR.
- [x] Type archived tetap dapat memberi label histori tetapi tidak dapat dipilih untuk input baru.

**Test:** `php artisan test --filter=EmployeeDocumentType`

**Dependencies:** persetujuan specification. **Scope:** M, 3–5 files.

**Hasil:** selesai 2026-07-13. Seeder idempotent menyediakan KTP, NPWP, passport, contract, certificate, medical, dan other. Catalog memisahkan pilihan input aktif dari resolver histori termasuk archived.

## Task 02 — Metadata-only create dan list ✅

**Tujuan:** memberi HR vertical path pertama untuk membuat dan melihat metadata dokumen tanpa upload.

**Files:** module contract; migration/model; request/DTO/service/transaction/controller/policy; page/types; feature test. Pecah backend dan frontend menjadi increment terpisah bila lebih dari lima file.

**Acceptance criteria:**

- [x] Authorized HR dapat membuat metadata berstatus `PENDING` dan melihat list paginated/filter employee/type/status.
- [x] Required-expiry, date order, duplicate policy, normalization, dan archived employee/type rules tervalidasi.
- [x] Nomor sensitif masked di list dan tidak muncul plaintext di audit; tidak ada media/file write.

**Test:** `php artisan test --filter=HREmployeeDocument && npm run typecheck && npm run build`

**Dependencies:** Task 01. **Scope:** L outcome; implement sebagai backend M lalu frontend M.

**Hasil:** selesai 2026-07-13. Nomor dokumen memakai encrypted cast, HMAC fingerprint, dan uniqueness key sesuai scope type; UI hanya menerima projection masked. Attachment/file tetap tidak tersedia.

## Task 03 — Mutation authorization matrix ✅

**Tujuan:** menutup seluruh metadata mutation dengan policy server-side dan audit actor.

**Files:** permissions/policy, authorization feature test, navigation, role sync contract.

**Acceptance criteria:**

- [x] Guest dan user tanpa permission ditolak untuk seluruh mutation.
- [x] `hr-officer`, `hr-manager`, dan `hr-viewer` hanya menerima permission yang didokumentasikan.
- [x] Frontend controls tidak dianggap security boundary.

**Test:** `php artisan test --filter=HREmployeeDocumentAuthorization`

**Dependencies:** Task 02. **Scope:** S/M, 2–4 files.

**Hasil:** selesai 2026-07-13. Matrix menginventaris mutation route aktual, membuktikan middleware auth, denial guest/user tanpa permission, zero write, exact seeded role contract, dan direct POST denial untuk viewer walaupun frontend menyembunyikan form.

## Checkpoint A — Approve vertical slice pertama

- [x] Task 01–03 hijau; review manusia menunggu konfirmasi checkpoint.
- [x] Tidak ada storage path, media collection, blob, atau direct DMS dependency.
- [x] Pint, lint, format, typecheck, build, module validation, dan backend suite hijau.

## Task 04 — Deterministic expiry query ✅

**Tujuan:** menghitung expiry state dengan tanggal dan warning window eksplisit.

**Files:** query/service, tests, list filter/types, specification.

**Acceptance criteria:**

- [x] `NOT_APPLICABLE`, `VALID`, `EXPIRING`, dan `EXPIRED` benar pada semua boundary.
- [x] Query tidak memakai waktu tersembunyi bila caller memberikan `asOf`.
- [x] Archived record dikecualikan secara default.

**Test:** `php artisan test --filter=EmployeeDocumentExpiry`

**Dependencies:** Task 02. **Scope:** M, 3–5 files.

**Hasil:** selesai 2026-07-13. List menerima `as_of`, `warning_days`, dan `expiry_state`; tanggal acuan yang telah di-resolve dikembalikan ke frontend. Classifier dan SQL filter memakai boundary yang sama, sedangkan soft-deleted metadata tidak ikut query default.

## Task 05 — Verification lifecycle ✅

**Tujuan:** menyediakan verify, reject, dan resubmit sebagai transition eksplisit dan atomic.

**Files:** lifecycle requests, service/transaction, routes/controller, frontend dialog, tests.

**Acceptance criteria:**

- [x] Transition matrix valid berhasil dengan actor/timestamp; reject mewajibkan reason.
- [x] Invalid/repeated transition tidak mengubah record atau membuat audit ganda.
- [x] Perubahan field material mengembalikan status ke `PENDING` dalam update yang sama.

**Test:** `php artisan test --filter=EmployeeDocumentVerification && npm run typecheck`

**Dependencies:** Task 03. **Scope:** pecah backend M dan frontend S.

**Hasil:** selesai 2026-07-13. Route verify/reject/resubmit memakai FormRequest authorization, transition allowlist, row lock, actor/timestamp, reason maksimal 1000 karakter, dan audit. Material identity/reference fields memiliki model invariant yang mereset review ke `PENDING`; dialog frontend hanya ditampilkan untuk permission verify/manage.

## Task 06 — Archive dan restore metadata ✅

**Tujuan:** menjaga histori metadata tanpa menyentuh file atau retention DMS.

**Files:** service/policy, controller/routes, tests, archive filter/dialog.

**Acceptance criteria:**

- [x] Archive memakai soft delete dan tidak ada force-delete route.
- [x] Archive/restore tidak memanggil delete DMS.
- [x] Restore menjalankan invariant duplicate/type kembali dan tercatat di audit.

**Test:** `php artisan test --filter=EmployeeDocumentArchive`

**Dependencies:** Task 02. **Scope:** M, pecah UI bila perlu.

**Hasil:** selesai 2026-07-13. Archive melepaskan active uniqueness claim tetapi mempertahankan encrypted history. Restore memakai row lock, memvalidasi employee/type aktif, menghitung ulang fingerprint/key, menolak duplicate, dan mencatat audit. Filter active/all/archived dan dialog frontend tersedia; force-delete serta operasi DMS tidak ada.

## Task 07 — Expiring read-only command ✅

**Tujuan:** menyediakan sumber reminder/report tanpa side effect notification.

**Files:** command, expiry service, provider registration, tests, README.

**Acceptance criteria:**

- [x] `--date` dan `--within` tervalidasi serta output reproducible.
- [x] Command tidak mengubah metadata, verification, audit, atau mengirim notification.
- [x] Exit code dan empty result terdokumentasi.

**Test:** `php artisan test --filter=EmployeeDocumentsExpiringCommand`

**Dependencies:** Task 04. **Scope:** M, 3–5 files.

**Hasil:** selesai 2026-07-13. `hr:documents-expiring` menerima tanggal valid `YYYY-MM-DD` dan window 0–3650 hari, memakai inclusive boundary yang sama dengan list, mengecualikan archived metadata, dan mengurutkan expiry/id. Output tidak memuat nomor dokumen. Match maupun empty result menghasilkan exit `0`; input invalid menghasilkan exit non-zero. Command terbukti tidak mengubah record, audit, atau notification.

## Checkpoint B — Metadata lifecycle complete

- [x] Task 04–07 hijau dan direview pada 2026-07-13.
- [x] Sensitive-data review memastikan masking, encryption, keyed fingerprint, dan audit redaction benar.
- [x] Full quality gates hijau: 251 backend tests/981 assertions, 4 frontend tests, build/lint/format/typecheck/Pint/module validation, serta production dependency audit tanpa advisory.

**Keputusan checkpoint:** approved. Metadata lifecycle dinyatakan complete. Task 08 belum boleh mengaktifkan attachment/storage; pekerjaan berikutnya hanya mendefinisikan DMS reference contract v1 dan tetap memerlukan persetujuan boundary lintas project.

## Task 08 — DMS reference contract v1 ✅

**Tujuan:** mendefinisikan interface/DTO lintas project tanpa import model atau schema internal DMS.

**Files:** Integration contracts/DTO/schema, fake adapter, contract tests, ADR/spec/module integration metadata.

**Acceptance criteria:**

- [x] Owner context memakai `schemaVersion: 1` dan field minimal.
- [x] Reference opaque; HR tidak menafsirkan ID atau menyimpan path/URL.
- [x] Missing, denied, archived, dan unavailable DMS memiliki semantics eksplisit.

**Test:** `php artisan test --filter=EmployeeDocumentDmsContract`

**Dependencies:** persetujuan interface Document Management. **Scope:** M, 3–5 files.

**Hasil:** selesai 2026-07-13. Contract read-only v1 menyediakan owner context empat field, opaque reference, descriptor lima state, normative JSON schema, fake adapter, dan module integration metadata. Fake tidak di-bind sebagai adapter production; route attachment/storage tetap belum ada.

## Task 09 — Attach dan detach reference

**Tujuan:** menghubungkan metadata HR ke logical document DMS secara idempotent tanpa partial state.

**Files:** attachment request/DTO, service/transaction, gateway adapter, controller/routes, tests, frontend control.

**Acceptance criteria:**

- [ ] Attach memakai idempotency/owner context dan menyimpan reference hanya setelah DMS sukses.
- [ ] Timeout/retry tidak membuat duplicate/orphan; detach tidak menghapus blob.
- [ ] Perubahan reference mereset verification ke `PENDING` dan diaudit tanpa URL/file detail sensitif.

**Test:** `php artisan test --filter=EmployeeDocumentAttachment`

**Dependencies:** Task 08 dan DMS foundation. **Scope:** pecah backend dan UI; masing-masing maksimal M.

## Task 10 — Secure access handoff

**Tujuan:** preview/download selalu melalui authorization dan delivery milik DMS.

**Files:** access adapter, controller handoff, policy tests, frontend action, integration docs.

**Acceptance criteria:**

- [ ] User harus lolos permission metadata HR dan access decision DMS.
- [ ] HR tidak menghasilkan storage URL atau melakukan stream blob sendiri.
- [ ] IDOR, expired delivery URL, missing reference, dan revoked access ditolak fail-closed.

**Test:** `php artisan test --filter=EmployeeDocumentAccess && npm run build`

**Dependencies:** Task 08–09 dan DMS access contract. **Scope:** M.

## Final quality checkpoint

```bash
php artisan module:validate
vendor/bin/pint --test
npm run lint:check
npm run format:check
npm run typecheck
npm run test:frontend
npm run build
php artisan test
git diff --check
```

- [ ] Semua acceptance criteria specification hijau.
- [ ] Tidak ada duplicate storage engine atau direct cross-project model import.
- [ ] Mutation denial matrix mencakup seluruh route baru.
- [ ] README/spec/plan/tasks/ADR sesuai perilaku aktual.
