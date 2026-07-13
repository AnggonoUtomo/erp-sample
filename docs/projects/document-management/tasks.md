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
- [x] Disk production, upload policy, dan malware strategy mendapat keputusan eksplisit sebelum Task 04.

**Decision:** ADR-002 dan ADR-003 accepted pada 2026-07-13. MVP memakai private-local single-server tanpa scanner. File valid boleh `AVAILABLE` dengan `scan_status=NOT_CONFIGURED`; status `CLEAN` dilarang tanpa scan aktual.

## Task 04 — Upload policy contract

**Tujuan:** membekukan boundary input sebelum menerima binary.

**Files:** policy/config DTO, validator, magic-byte detector adapter, tests, ADR/spec adjustment.

**Acceptance criteria:**

- [x] Size, extension, declared/detected MIME, filename, empty stream, dan allowed types tervalidasi.
- [x] Path traversal, double extension, MIME spoof, polyglot policy, dan oversized input memiliki semantics eksplisit.
- [x] Risk acceptance tanpa scanner menghasilkan `scan_status=NOT_CONFIGURED`, compensating controls, dan larangan memalsukan status `CLEAN`.

**Test:** `php artisan test --filter=DocumentUploadPolicy`

**Dependencies:** Checkpoint A dan keputusan security. **Scope:** M.

**Completed:** 2026-07-13 — bounded seekable-stream inspection memvalidasi maksimum 20 MiB, safe filename, PDF/JPEG/PNG matrix, exact magic-byte, terminal marker, no trailing payload, actual/declaration size, bounded idempotency key, dan `NOT_CONFIGURED` scan status. Tidak ada route atau storage write. Verifikasi: `DocumentUploadPolicyTest` (5 test, 26 assertions) lulus.

## Task 05 — Staged ingestion vertical slice

**Tujuan:** menghasilkan logical document + version pertama + checksum secara atomic/idempotent.

**Files:** request/DTO, ingestion service/transaction, controller/route, fake storage failure tests; UI dipisah.

**Acceptance criteria:**

- [x] Valid stream menghasilkan satu `AVAILABLE` version dengan `scan_status=NOT_CONFIGURED` dan opaque reference pada MVP.
- [x] Retry identik mengembalikan result yang sama; key sama dengan fingerprint berbeda ditolak conflict.
- [x] Validation/storage/checksum failure tidak meninggalkan active partial document dan staged cleanup/reconciliation tercatat.

**Test:** `php artisan test --filter=DocumentIngestion`

**Dependencies:** Task 03–04. **Scope:** L outcome, pecah menjadi increment backend M.

**Completed:** 2026-07-13 — endpoint internal terautentikasi membuat logical document dan immutable version pertama melalui alur validate/hash, private stage, transaction metadata/idempotency, promote, lalu publish `AVAILABLE`. Retry identik tidak menggandakan row/object; fingerprint berbeda menghasilkan conflict. Failure injection pada stage dan promote membuktikan rollback database serta cleanup storage tanpa partial aktif. Audit hanya mencatat identifier/status aman dan UI belum dibuat. Verifikasi: `DocumentIngestionTest` (6 test, 37 assertions), regression terarah (34 test, 166 assertions), Pint, dan `module:validate` lulus.

## Task 06 — Immutable replacement version

**Tujuan:** menambah version tanpa overwrite binary sebelumnya.

**Files:** version service/transaction, route/request, tests, optional minimal UI.

**Acceptance criteria:**

- [x] Version number monotonik dan current version berganti atomic.
- [x] Failure mempertahankan current version lama.
- [x] Old version tetap private/readable sesuai policy dan tidak dihapus.

**Test:** `php artisan test --filter=DocumentVersioning`

**Dependencies:** Task 05. **Scope:** M.

**Completed:** 2026-07-13 — endpoint replacement terautentikasi dengan permission `documents.replace` membuat version berikutnya di bawah document lock tanpa overwrite metadata/blob lama. Current pointer hanya berganti setelah private promotion sukses; retry identik idempotent dan fingerprint berbeda conflict. Guard model melarang perubahan integrity metadata version `AVAILABLE`. Failure sebelum dan sesudah storage move membuktikan transaction rollback, cleanup object baru, serta current version/binary lama tetap utuh. Verifikasi: `DocumentVersioningTest` (6 test), checkpoint regression (31 test, 170 assertions), Pint, dan `module:validate` lulus.

## Checkpoint B — Ingestion integrity

- [x] Task 04–06 hijau, failure injection direview, dan no-orphan evidence tersedia.
- [x] Checksum, idempotency, privacy, risk acceptance tanpa scanner, dan private disk review approved.
- [x] Production ingestion disabled sampai seluruh keputusan deployment terpenuhi.

**Approved:** 2026-07-13 — bukti review, residual risk, dan checklist aktivasi tercatat pada [Checkpoint B — Ingestion integrity](checkpoint-b-ingestion-integrity.md). Production default fail-closed melalui `DMS_INGESTION_ENABLED=false`; activation adalah keputusan deployment eksplisit, bukan efek commit ini.

## Task 07 — Archive, restore, dan descriptor

**Tujuan:** menyediakan lifecycle logical document serta read-only state contract.

**Files:** policy/service/routes, descriptor adapter, tests, minimal filter/UI bila diperlukan.

**Acceptance criteria:**

- [x] Archive soft-delete/state transition; restore revalidasi owner/version.
- [x] Tidak ada force delete/permanent blob deletion route.
- [x] Reader mengembalikan `AVAILABLE|MISSING|ARCHIVED|UNAVAILABLE|DENIED` deterministik.

**Test:** `php artisan test --filter=DocumentLifecycle`

**Dependencies:** Task 05. **Scope:** M.

**Completed:** 2026-07-13 — archive dan restore memakai row lock, permission terpisah, actor/reason tervalidasi, audit aman, serta transition idempotent. Archive hanya soft-delete logical metadata dan tidak mengubah/menghapus version atau private object. Restore memvalidasi ulang owner context schema, current version ownership/status, dan keberadaan object; kegagalan mempertahankan state `ARCHIVED`. `DatabaseDocumentReferenceReader` mengembalikan descriptor v1 deterministik tanpa storage detail dan fail-closed menjadi `UNAVAILABLE`/`DENIED`. Tidak ada force-delete route. Verifikasi: `DocumentLifecycleTest` (6 test), regression contract/storage/consumer (37 test, 201 assertions), Pint, dan `module:validate` lulus.

## Task 08 — Access decision matrix

**Tujuan:** membuat DMS menjadi security authority untuk binary.

**Files:** access DTO/policy/service, permission manifest, denial matrix tests.

**Acceptance criteria:**

- [x] Actor/action/reference/owner/state diperiksa server-side.
- [x] Guest, unauthorized, IDOR, missing, archived, unavailable, dan revoked ditolak fail-closed.
- [x] Consumer permission tidak dapat menggantikan DMS decision.

**Test:** `php artisan test --filter=DocumentAccessDecision`

**Dependencies:** Task 07. **Scope:** M.

**Completed:** 2026-07-13 — `DocumentAccessGateway` v1, request/decision DTO, policy action-permission, dan service authority DMS diterbitkan melalui module manifest. Evaluasi permission dilakukan sebelum lookup reference; expected owner harus exact; lifecycle/version/private-object diperiksa server-side. Unknown action, guest, consumer-only permission, IDOR, permission revoked, missing, archived, unavailable, dan storage error seluruhnya tidak menghasilkan access grant. Grant/denial diaudit tanpa owner/storage secrets dan tanpa fallback actor session yang salah. Matrix normatif: [Document access decision matrix v1](access-decision-matrix.md). Verifikasi: `DocumentAccessDecisionTest` (6 test), contract regression (25 test, 114 assertions), module validation, Pint, dan full backend suite lulus.

## Task 09 — Secure delivery handoff

**Tujuan:** mengirim binary tanpa mengekspos storage credential/path.

**Files:** delivery DTO/token service/controller, storage read adapter, tests; frontend consumer terpisah.

**Acceptance criteria:**

- [x] Delivery scoped ke actor/reference/action, short-lived, dan tidak reusable di luar contract.
- [x] Expired/revoked/replayed/wrong-action token ditolak.
- [x] Token, object key, dan storage URL tidak masuk audit/log/consumer database.

**Test:** `php artisan test --filter=DocumentDelivery`

**Dependencies:** Task 08 dan keputusan streaming/presigned URL. **Scope:** M/L, split wajib.

**Completed:** 2026-07-14 — `DocumentDeliveryGateway` v1 menerbitkan handoff satu kali ber-TTL 300 detik untuk action `DOWNLOAD`. Raw token hanya dikembalikan saat issue; database DMS menyimpan HMAC token dan owner fingerprint, sedangkan audit tidak memuat token/object key/URL. Consume melalui authenticated POST body, row lock, revalidasi permission/owner/current version, lalu private stream dengan attachment dan header anti-sniff/no-store. Expired, revoked, replayed, wrong actor/action, permission revoked, dan version berubah seluruhnya fail-closed. Keputusan: [ADR-004](decisions/004-one-time-secure-delivery.md). Verifikasi: `DocumentDeliveryTest` (5 test, 36 assertions), contract/module regression, Pint, dan quality gates backend lulus.

## Checkpoint C — Secure DMS foundation

- [x] Task 07–09 dan global mutation/access denial matrix hijau.
- [x] Security review upload/download, dependency audit, backup/restore, dan observability approved.
- [x] Full quality gates hijau.

**Approved:** 2026-07-14 — evidence, residual risks, production gates, serta upgrade signed full-backup v3 yang mencakup private DMS binary dicatat pada [Checkpoint C — Secure DMS foundation](checkpoint-c-secure-dms-foundation.md). Checkpoint membuka Task 10 adapter HR, tetapi tidak mengaktifkan ingestion production.

## Task 10 — HR attachment adapter

**Tujuan:** menyediakan implementation `EmployeeDocumentAttachmentGateway` tanpa direct model import.

**Files:** DMS-owned adapter/contract DTO, HR binding configuration, contract/failure tests, integration documentation.

**Acceptance criteria:**

- [x] Owner context v1 dan idempotency diteruskan tanpa employee profile/PII tambahan.
- [x] Success mengembalikan opaque reference hanya setelah DMS version available.
- [x] Timeout/missing/denied/unavailable tidak membuat partial HR reference atau orphan aktif.

**Test:** `php artisan test --filter=EmployeeDocumentAttachmentGateway`

**Dependencies:** Checkpoint C. **Scope:** M.

**Completed:** 2026-07-14 — DMS mengekspor generic `DocumentIngestionGateway` v1; consumer HR memiliki `EmployeeDocumentAttachmentGateway` dan adapter yang menerjemahkan owner context minimal tanpa import model/storage DMS atau profile/PII employee. Retry identik mengembalikan reference sama setelah version `AVAILABLE`; timeout, non-available result, dan storage failure fail-closed serta tidak meninggalkan row/object aktif. Penempatan adapter di consumer menjaga dependency HR → public DMS contract; lihat [HR ADR-004](../hr/employee-documents/decisions/004-consumer-owned-dms-attachment-adapter.md). Verifikasi: `EmployeeDocumentAttachmentGatewayTest` (7 test, 32 assertions), module validation, Pint, dan regression suite lulus.

## Task 11 — Buka kembali HR Task 09 dan Task 10

**Tujuan:** mengintegrasikan attach/detach dan secure access handoff end-to-end.

**Files:** mengikuti task HR, bukan memperluas model internal DMS.

**Acceptance criteria:**

- [x] Employee Documents Task 09 dan 10 seluruhnya hijau.
- [x] Tidak ada path/URL/file metadata DMS disalin ke tabel HR.
- [x] Detach/archive HR tidak menghapus blob; DMS tetap security authority.

**Test:** suite `EmployeeDocumentAttachment`, `EmployeeDocumentAccess`, dan full quality gates.

**Dependencies:** Task 10. **Scope:** dua vertical slice terpisah.

**Completed:** 2026-07-14 — HR Task 09 attach/detach dan Task 10 secure access handoff dibuka kembali dan selesai sebagai dua commit/slice. HR menyimpan opaque reference + schema version dan HMAC idempotency reservation saja; DMS tetap memiliki version, storage, integrity, access decision, delivery, dan binary stream. Dual permission, exact owner, timeout retry, detach preservation, IDOR, expiry, replay, dan revoke dibuktikan executable tests. Dokumentasi: [Employee Documents README](../hr/employee-documents/README.md), [ADR-004](../hr/employee-documents/decisions/004-consumer-owned-dms-attachment-adapter.md), dan [ADR-005](../hr/employee-documents/decisions/005-dual-authority-secure-delivery.md).

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

- [x] Semua acceptance criteria specification hijau.
- [x] Tidak ada public disk, binary DB column, direct storage URL, atau cross-project model import.
- [x] Mutation/access denial matrix mencakup seluruh route.
- [x] README/spec/plan/tasks/ADR sesuai implementation aktual.

**Approved:** 2026-07-14 — seluruh backend/frontend/module/security gate hijau tanpa finding blocking. Evidence review, hasil test/audit, batas deployment, dan residual risk dicatat pada [Final Quality Checkpoint](final-quality-checkpoint.md). Foundation Task 01–11 selesai; ingestion production tetap memerlukan aktivasi eksplisit sesuai Checkpoint B.
