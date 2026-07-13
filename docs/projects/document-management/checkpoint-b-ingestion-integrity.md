# Checkpoint B — Ingestion integrity

## Status

Approved — 2026-07-13.

Checkpoint ini menyetujui integrity foundation Task 04–06 untuk dilanjutkan ke lifecycle Task 07. Persetujuan bukan izin otomatis untuk production: ingestion production tetap fail-closed sampai operator menyelesaikan checklist aktivasi dan menetapkan `DMS_INGESTION_ENABLED=true` secara eksplisit.

## Bukti review

| Area | Bukti | Hasil |
|---|---|---|
| Input boundary | Maksimum 20 MiB, PDF/JPEG/PNG, extension–declared MIME–magic-byte cocok, terminal marker dan trailing payload diperiksa | Approved |
| Integrity | SHA-256 dan observed byte size dihitung server-side pada stream bounded sebelum staging | Approved |
| Idempotency | Raw key di-HMAC; retry fingerprint identik memakai result lama, fingerprint berbeda `409` | Approved |
| Atomic publish | Metadata, version, current pointer, dan idempotency berada dalam transaction; publish baru visible setelah promotion sukses | Approved |
| Failure/no orphan | Failure stage, promote, dan failure setelah move membuktikan rollback database dan cleanup object baru | Approved |
| Immutable history | Replacement menambah nomor version monotonik; metadata/blob version `AVAILABLE` lama tidak dioverwrite dan tetap readable | Approved |
| Privacy | Response hanya opaque reference/version/status; audit tidak memuat filename, checksum, object key, path, atau public URL | Approved |
| Authorization | Upload dan replacement membutuhkan authentication, permission terpisah, dan throttle | Approved |
| Private storage | Adapter menolak disk non-local, public, served, atau berkonfigurasi URL | Approved untuk single-server MVP |
| Scanner | `scan_status=NOT_CONFIGURED`, bukan `CLEAN`; residual risk diterima melalui ADR-003 | Approved dengan accepted risk |
| Production gate | Service boundary menolak create/replace sebelum validasi atau storage write ketika gate mati | Approved; default production disabled |

## Aktivasi production

Sebelum mengubah `DMS_INGESTION_ENABLED=true`, operator wajib memastikan:

1. Deployment hanya satu application server dan memakai private-local disk sesuai ADR-001/ADR-003.
2. `DMS_PRIVATE_DISK` menunjuk disk local dengan `serve=false`, visibility `private`, tanpa URL publik, serta directory writable oleh application user.
3. Batas request web server/PHP dan timeout sesuai maksimum aplikasi 20 MiB.
4. Kapasitas disk, backup/restore private storage, monitoring kapasitas, dan prosedur pemulihan telah diuji pada environment target.
5. Permission `documents.upload` dan `documents.replace` hanya diberikan kepada role yang disetujui.
6. Owner menerima residual risk MVP tanpa malware scanner dan seluruh trigger evaluasi ADR-003 dipahami.
7. Inline preview, OCR, thumbnail, parsing, conversion, format tambahan, dan multi-server tetap nonaktif.

Jika salah satu syarat tidak terpenuhi, biarkan `DMS_INGESTION_ENABLED=false`. Perubahan topology, format, parsing, atau insiden security wajib membuka evaluasi ADR baru.

## Verifikasi

```bash
php artisan test --filter="DocumentUploadPolicy|DocumentIngestion|DocumentVersioning|DocumentStorageAdapter"
php artisan module:validate
vendor/bin/pint --test
composer audit --locked
git diff --check
```

Relasi: [Specification](specification.md), [Implementation plan](implementation-plan.md), [Tasks](tasks.md), [ADR-001](decisions/001-single-private-storage-engine.md), [ADR-002](decisions/002-upload-security-policy.md), dan [ADR-003](decisions/003-mvp-single-server-without-malware-scanner.md).
