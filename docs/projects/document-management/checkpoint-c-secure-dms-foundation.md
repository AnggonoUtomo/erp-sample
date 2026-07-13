# Checkpoint C — Secure DMS foundation

## Status

Approved — 2026-07-14.

Checkpoint ini menyatakan foundation DMS aman untuk dilanjutkan ke adapter HR. Ini bukan persetujuan deployment production atau aktivasi ingestion production.

## Evidence

### Authorization dan delivery

- Seluruh route mutasi `document-management.*` masuk inventaris global `MutationRouteAuthorizationTest` dan wajib memiliki middleware `auth`.
- Permission denial, IDOR, owner mismatch, missing, archived, unavailable, revoked permission, expired/revoked/replayed token, wrong actor/action, dan current-version change diuji fail-closed.
- Delivery hanya melalui controller DMS dengan one-time token, revalidasi akses, attachment, `no-store`, `nosniff`, dan CSP `sandbox`.
- Audit issue/consume/revoke tidak memuat raw token, owner fingerprint, storage object key, URL, atau binary.

### Upload dan storage

- Upload dibatasi 20 MiB serta PDF/JPEG/PNG; extension, declared MIME, magic-byte, terminal marker, dan observed size harus cocok.
- Object key dibuat server, private-local disk tidak served, version immutable, dan failure injection membuktikan rollback/cleanup tanpa active partial reference.
- Risiko MVP tanpa malware scanner tetap diterima melalui ADR-003; status wajib `NOT_CONFIGURED`, bukan `CLEAN`.

### Backup dan restore

- Full-backup schema v3 mencakup database, `storage_public`, dan `storage_dms_private`.
- Semua payload berada dalam signed manifest dan memiliki SHA-256; forged/tampered/path-traversal archive ditolak sebelum destructive write.
- Round-trip test membuktikan binary `storage/app/private/document-management` dapat dipulihkan.
- Backup v2 adalah format historis sebelum DMS private binary. Restore saat ini menolaknya agar tidak memberi kesan recovery DMS lengkap.

### Observability dan dependencies

- Create/version/archive/restore/access/delivery serta full backup/restore memiliki audit event dengan metadata allowlist.
- `composer audit --locked` dan `npm audit --omit=dev` wajib hijau pada verifikasi checkpoint.
- Tidak ada dependency baru pada Task 07–09 maupun compatibility backup ini.

## Residual risks dan production gates

- Malware scanner belum tersedia sesuai risk acceptance ADR-003.
- Private-local single-server belum memiliki replication/high availability.
- Full export/restore harus dijalankan pada maintenance window yang menghentikan mutasi DMS dan diverifikasi pada salinan environment; database dan filesystem tidak memiliki snapshot/transaction lintas resource.
- Format v3 saat ini dibatasi 10.000 entry dan total 512 MiB uncompressed. Sebelum volume mendekati batas, storage/backup topology wajib dievaluasi dan tidak boleh sekadar menaikkan limit tanpa threat review.
- Batas PHP/web-server upload dan timeout harus mendukung signed ZIP sampai kebijakan aplikasi 512 MiB.
- Range request, resume download, inline preview, CDN, public share, retention deletion, dan legal hold tetap non-scope.
- `DMS_INGESTION_ENABLED` tetap `false` di production sampai checklist deployment dan smoke restore environment target disetujui operator.

## Verification

```bash
php artisan test --filter="Document|BackupRestore|MutationRouteAuthorization"
php artisan module:validate
vendor/bin/pint --test
npm run quality:check
php artisan test --compact
npm audit --omit=dev
composer audit --locked
git diff --check
```

## Relevansi

- [Tasks](tasks.md)
- [Specification](specification.md)
- [Access decision matrix](access-decision-matrix.md)
- [ADR-003: MVP tanpa scanner](decisions/003-mvp-single-server-without-malware-scanner.md)
- [ADR-004: One-time delivery](decisions/004-one-time-secure-delivery.md)
- [Global mutation authorization matrix](../../reviews/2026-07-11-project-baseline/08-mutation-authorization-matrix.md)
- [Backup signature runbook](../../reviews/2026-07-11-project-baseline/09-backup-signature-runbook.md)
