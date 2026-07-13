# Tasks: Document Management Foundation

Implementasi wajib berurutan dan berhenti pada setiap checkpoint. Setiap task adalah satu outcome kecil; jika file aktual melampaui scope, pecah backend/storage/UI menjadi commit terpisah.

## Task 01 — Module dan contract boundary

**Tujuan:** membuat module foundation tanpa upload/storage behavior.

**Files:** module manifest/provider, permission contract, Integration DTO/schema, architecture/contract tests, docs bila keputusan berubah.

**Acceptance criteria:**

- [x] Module validator mengenali `DocumentManagement/Foundation` dan dependency contract.
- [x] Owner context/reference/result v1 typed, versioned, minimal, dan additive.
- [x] Architecture test melarang binary DB field, public URL/path exposure, dan direct consumer model import.

**Test:** `php artisan test --filter=DocumentManagementContract && php artisan module:validate`

**Dependencies:** specification dan ADR-001 approved. **Scope:** M, 3–5 files per increment.

**Completed:** 2026-07-13 — contract-only module; tidak ada route, migration, model, navigation, atau storage behavior. Verifikasi: `DocumentManagementFoundationContractTest` (5 test, 26 assertions) dan `module:validate` lulus.

## Task 02 — Logical document metadata

**Tujuan:** menyimpan logical document, opaque reference, owner context, dan lifecycle metadata tanpa binary.

**Files:** migration/model, repository/service, DTO, tests.

**Acceptance criteria:**

- [x] Reference unique/immutable; owner context tervalidasi dan indexed.
- [x] Create idempotent terhadap owner context + idempotency key.
- [x] Tidak ada disk/path/binary/public URL pada output contract.

**Test:** `php artisan test --filter=DocumentManagementLogicalDocument`

**Dependencies:** Task 01. **Scope:** M.

**Completed:** 2026-07-13 — logical metadata `PENDING`, opaque ULID reference, indexed owner context, dan HMAC idempotency record dibuat dalam satu transaction. Tidak ada binary, disk/path, route, atau public URL. Verifikasi: `DocumentManagementLogicalDocumentTest` (5 test, 16 assertions) lulus.

## Task 03 — Private StorageAdapter contract

**Tujuan:** memisahkan binary I/O dari domain dan menjamin private-only behavior.

**Files:** storage interface/DTO, fake adapter, private local adapter, contract tests, config documentation.

**Acceptance criteria:**

- [x] Adapter mendukung staged write/read/exists/promote/delete-staged tanpa menghasilkan public URL.
- [x] Object key selalu server-generated dan tidak berasal dari filename/path input.
- [x] Fake dan local adapter lulus contract test yang sama.

**Test:** `php artisan test --filter=DocumentStorageAdapter`

**Dependencies:** Task 01. **Scope:** M; belum ada HTTP upload.

**Completed:** 2026-07-13 — fake dan private-local adapter memakai server-generated staged/object ULID key, readable stream, promote, read, exists, dan idempotent staged cleanup. Konfigurasi public/served fail-closed. Verifikasi: `DocumentStorageAdapterTest` (5 test, 34 assertions) lulus.

## Checkpoint A — Safe metadata foundation

- [x] Task 01–03 hijau dan direview.
- [x] Migration/module/storage contract valid; upload route belum ada.
- [ ] Disk production, upload policy, dan malware strategy mendapat keputusan eksplisit sebelum Task 04.

## Task 04 — Upload policy contract

**Tujuan:** membekukan boundary input sebelum menerima binary.

**Files:** policy/config DTO, validator, magic-byte detector adapter, tests, ADR/spec adjustment.

**Acceptance criteria:**

- [ ] Size, extension, declared/detected MIME, filename, empty stream, dan allowed types tervalidasi.
- [ ] Path traversal, double extension, MIME spoof, polyglot policy, dan oversized input memiliki semantics eksplisit.
- [ ] Malware decision menghasilkan `QUARANTINED`/rejection contract, bukan bypass.

**Test:** `php artisan test --filter=DocumentUploadPolicy`

**Dependencies:** Checkpoint A dan keputusan security. **Scope:** M.

## Task 05 — Staged ingestion vertical slice

**Tujuan:** menghasilkan logical document + version pertama + checksum secara atomic/idempotent.

**Files:** request/DTO, ingestion service/transaction, controller/route, fake storage failure tests; UI dipisah.

**Acceptance criteria:**

- [ ] Valid stream menghasilkan satu `AVAILABLE` version dan opaque reference.
- [ ] Retry identik mengembalikan result yang sama; key sama dengan fingerprint berbeda ditolak conflict.
- [ ] Validation/storage/checksum failure tidak meninggalkan active partial document dan staged cleanup/reconciliation tercatat.

**Test:** `php artisan test --filter=DocumentIngestion`

**Dependencies:** Task 03–04. **Scope:** L outcome, pecah menjadi increment backend M.

## Task 06 — Immutable replacement version

**Tujuan:** menambah version tanpa overwrite binary sebelumnya.

**Files:** version service/transaction, route/request, tests, optional minimal UI.

**Acceptance criteria:**

- [ ] Version number monotonik dan current version berganti atomic.
- [ ] Failure mempertahankan current version lama.
- [ ] Old version tetap private/readable sesuai policy dan tidak dihapus.

**Test:** `php artisan test --filter=DocumentVersioning`

**Dependencies:** Task 05. **Scope:** M.

## Checkpoint B — Ingestion integrity

- [ ] Task 04–06 hijau, failure injection direview, dan no-orphan evidence tersedia.
- [ ] Checksum, idempotency, privacy, malware/quarantine, dan private disk review approved.
- [ ] Production ingestion disabled sampai seluruh keputusan deployment terpenuhi.

## Task 07 — Archive, restore, dan descriptor

**Tujuan:** menyediakan lifecycle logical document serta read-only state contract.

**Files:** policy/service/routes, descriptor adapter, tests, minimal filter/UI bila diperlukan.

**Acceptance criteria:**

- [ ] Archive soft-delete/state transition; restore revalidasi owner/version.
- [ ] Tidak ada force delete/permanent blob deletion route.
- [ ] Reader mengembalikan `AVAILABLE|MISSING|ARCHIVED|UNAVAILABLE|DENIED` deterministik.

**Test:** `php artisan test --filter=DocumentLifecycle`

**Dependencies:** Task 05. **Scope:** M.

## Task 08 — Access decision matrix

**Tujuan:** membuat DMS menjadi security authority untuk binary.

**Files:** access DTO/policy/service, permission manifest, denial matrix tests.

**Acceptance criteria:**

- [ ] Actor/action/reference/owner/state diperiksa server-side.
- [ ] Guest, unauthorized, IDOR, missing, archived, unavailable, dan revoked ditolak fail-closed.
- [ ] Consumer permission tidak dapat menggantikan DMS decision.

**Test:** `php artisan test --filter=DocumentAccessDecision`

**Dependencies:** Task 07. **Scope:** M.

## Task 09 — Secure delivery handoff

**Tujuan:** mengirim binary tanpa mengekspos storage credential/path.

**Files:** delivery DTO/token service/controller, storage read adapter, tests; frontend consumer terpisah.

**Acceptance criteria:**

- [ ] Delivery scoped ke actor/reference/action, short-lived, dan tidak reusable di luar contract.
- [ ] Expired/revoked/replayed/wrong-action token ditolak.
- [ ] Token, object key, dan storage URL tidak masuk audit/log/consumer database.

**Test:** `php artisan test --filter=DocumentDelivery`

**Dependencies:** Task 08 dan keputusan streaming/presigned URL. **Scope:** M/L, split wajib.

## Checkpoint C — Secure DMS foundation

- [ ] Task 07–09 dan global mutation/access denial matrix hijau.
- [ ] Security review upload/download, dependency audit, backup/restore, dan observability approved.
- [ ] Full quality gates hijau.

## Task 10 — HR attachment adapter

**Tujuan:** menyediakan implementation `EmployeeDocumentAttachmentGateway` tanpa direct model import.

**Files:** DMS-owned adapter/contract DTO, HR binding configuration, contract/failure tests, integration documentation.

**Acceptance criteria:**

- [ ] Owner context v1 dan idempotency diteruskan tanpa employee profile/PII tambahan.
- [ ] Success mengembalikan opaque reference hanya setelah DMS version available.
- [ ] Timeout/missing/denied/unavailable tidak membuat partial HR reference atau orphan aktif.

**Test:** `php artisan test --filter=EmployeeDocumentAttachmentGateway`

**Dependencies:** Checkpoint C. **Scope:** M.

## Task 11 — Buka kembali HR Task 09 dan Task 10

**Tujuan:** mengintegrasikan attach/detach dan secure access handoff end-to-end.

**Files:** mengikuti task HR, bukan memperluas model internal DMS.

**Acceptance criteria:**

- [ ] Employee Documents Task 09 dan 10 seluruhnya hijau.
- [ ] Tidak ada path/URL/file metadata DMS disalin ke tabel HR.
- [ ] Detach/archive HR tidak menghapus blob; DMS tetap security authority.

**Test:** suite `EmployeeDocumentAttachment`, `EmployeeDocumentAccess`, dan full quality gates.

**Dependencies:** Task 10. **Scope:** dua vertical slice terpisah.

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
npm audit --omit=dev
composer audit --locked
git diff --check
```

- [ ] Semua acceptance criteria specification hijau.
- [ ] Tidak ada public disk, binary DB column, direct storage URL, atau cross-project model import.
- [ ] Mutation/access denial matrix mencakup seluruh route.
- [ ] README/spec/plan/tasks/ADR sesuai implementation aktual.
