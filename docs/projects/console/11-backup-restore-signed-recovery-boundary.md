# 11 — Backup Restore Signed Recovery Boundary

Dokumen ini mencatat hasil telusur `Console.BackupRestores` seolah-olah modul ini baru akan dibuat. Fokusnya adalah memastikan backup/restore menjadi recovery boundary yang aman: permission-gated, signed, checksum-verified, menolak format lama/unsafe, mencakup private Document Management, dan memiliki dry-run eksplisit sebelum destructive restore.

## Status

`Reviewed and hardened — 2026-07-19`.

Task dinyatakan selesai. Ada hardening kecil:

- full restore sekarang punya `dry_run` eksplisit yang memvalidasi ZIP tanpa menulis database/storage;
- UI full restore default ke dry-run;
- audit event membedakan `full_backup.dry_run_validated` dan `full_backup.restored`;
- pesan settings restore yang menemukan manifest full backup diperbaiki agar tidak lagi menyebut raw `.sql`, karena full restore aktif hanya menerima signed `.zip`.

## Urutan baca relevan

1. [Checkpoint D — Runtime Operation Boundary](checkpoint-d-runtime-operation-boundary.md) — checkpoint sebelum recovery boundary.
2. Dokumen ini — boundary Backup Restore.
3. [Backup signature runbook](../../reviews/2026-07-11-project-baseline/09-backup-signature-runbook.md) — runbook provisioning key, restore drill, dan rotasi.
4. [ADR-004: Full Backup V2 Security Boundary](../../reviews/2026-07-11-project-baseline/decisions/004-full-backup-v2-security-boundary.md) — riwayat hardening checksum/ZIP boundary.
5. [ADR-005: Cross-environment Full Backup Signatures](../../reviews/2026-07-11-project-baseline/decisions/005-cross-environment-backup-signatures.md) — HMAC signature lintas environment.
6. [ADR-006: Full Backup V3 mencakup private DMS](../../reviews/2026-07-11-project-baseline/decisions/006-full-backup-v3-private-dms.md) — private Document Management masuk exact signed payload.
7. Nanti: [Checkpoint E — Recovery boundary](tasks.md#checkpoint-e--recovery-boundary).

## Source yang ditelusuri

- `app/Modules/Console/BackupRestores/module.php`
- `app/Modules/Console/BackupRestores/routes.php`
- `app/Modules/Console/BackupRestores/permissions.php`
- `app/Modules/Console/BackupRestores/navigation.php`
- `app/Modules/Console/BackupRestores/Http/Controllers/BackupRestoreController.php`
- `app/Modules/Console/BackupRestores/Http/Requests/RestoreBackupRequest.php`
- `app/Modules/Console/BackupRestores/Http/Requests/FullRestoreBackupRequest.php`
- `app/Modules/Console/BackupRestores/Policies/BackupRestorePolicy.php`
- `app/Modules/Console/BackupRestores/Services/BackupRestoreService.php`
- `app/Modules/Console/BackupRestores/Services/SettingsBackupService.php`
- `app/Modules/Console/BackupRestores/Services/FullBackupZipService.php`
- `app/Modules/Console/BackupRestores/Services/FullBackupArchiveValidator.php`
- `app/Modules/Console/BackupRestores/Services/BackupSignatureService.php`
- `app/Modules/Console/BackupRestores/Services/SqlDumpExecutor.php`
- `resources/js/pages/console/backup-restore/index.tsx`
- `resources/js/pages/console/backup-restore/types.ts`
- `resources/js/pages/console/backup-restore/backup-restore-components/backup-restore-header.tsx`
- `resources/js/pages/console/backup-restore/backup-restore-components/backup-summary-cards.tsx`
- `tests/Feature/BackupRestoreTest.php`
- `config/backup.php`

## Contract modul

`Console.BackupRestores` adalah recovery module untuk:

1. settings backup JSON;
2. settings restore JSON;
3. full backup signed ZIP;
4. full restore signed ZIP;
5. dry-run full restore.

Boundary utama:

- route wajib `auth`;
- setiap action wajib permission khusus;
- settings backup berbeda dari full backup;
- settings backup hanya mencakup system settings dan notification templates;
- full backup aktif adalah `laravel12-starterkit.full-backup` versi 3;
- full restore hanya menerima signed `.zip`;
- raw SQL/legacy unsigned restore ditolak;
- ZIP divalidasi sebelum write;
- signature dan checksum diverifikasi sebelum SQL/filesystem write;
- dry-run memvalidasi arsip tanpa menulis database/storage;
- full restore database logout user karena session/database dapat berubah.

## Module export

| Export | Status | Catatan |
|---|---:|---|
| `routes` | yes | index, settings export/restore, full export/restore |
| `permissions` | yes | view/export/restore/full-export/full-restore |
| `navigation` | yes | menu Sistem → Backup & Restore |
| `events/listeners` | no | recovery action langsung |
| `integrations` | no | backup format adalah internal recovery contract |

## Route dan permission

| Route | Method | Aksi | Guard |
|---|---:|---|---|
| `/backup-restore` | `GET` | halaman overview | `auth` + `backup-restore.view` |
| `/backup-restore/export` | `GET` | export settings JSON | `auth` + `backup-restore.export` |
| `/backup-restore/restore` | `POST` | restore settings JSON | `auth` + `backup-restore.restore` |
| `/backup-restore/full/export` | `GET` | export full signed ZIP | `auth` + `backup-restore.full-export` |
| `/backup-restore/full/restore` | `POST` | dry-run atau restore full signed ZIP | `auth` + `backup-restore.full-restore` + `throttle:3,10` |

Default role mapping:

- `admin`: `backup-restore.view`, `backup-restore.export`;
- `staff`: tidak mendapat permission default.

Full restore sengaja tidak menjadi permission default admin pada module definition. Permission ini harus diberikan eksplisit untuk operator yang benar-benar dipercaya.

## Settings backup JSON

Format:

```json
{
  "schema": "laravel12-starterkit.settings-backup",
  "version": 1,
  "exported_at": "...",
  "app": {
    "name": "...",
    "url": "...",
    "environment": "..."
  },
  "sections": {
    "system_settings": [],
    "notification_templates": []
  }
}
```

Included sections:

- `system_settings`;
- `notification_templates`.

Excluded sections:

- users;
- roles/permissions;
- audit logs;
- login activities;
- media/binary files;
- full database.

Catatan penting: encrypted system setting diekspor sesuai nilai yang tersimpan. Settings backup bukan format recovery lintas app key; untuk disaster recovery gunakan full signed ZIP dan runbook environment yang benar.

## Full backup signed ZIP v3

Format aktif:

- schema: `laravel12-starterkit.full-backup`;
- version: `3`;
- archive: `.zip`;
- manifest: `manifest.json`;
- database dump: `database.sql`;
- storage public namespace: `storage_public/*`;
- private DMS namespace: `storage_dms_private/*`;
- authenticity: HMAC-SHA256 atas canonical manifest;
- integrity: SHA-256 exact entry list.

Payload ZIP:

| Entry | Fungsi |
|---|---|
| `manifest.json` | schema, version, app metadata, database metadata, includes, checksum, signature |
| `database.sql` | dump database dari aplikasi |
| `storage_public/*` | file `storage/app/public` |
| `storage_dms_private/*` | binary private Document Management dari `storage/app/private/document-management` |

Full backup dibuat melalui `BackupRestoreService::createFullBackupZip()`:

1. dump database dibuat dari koneksi aktif MySQL atau SQLite;
2. storage public dan private DMS di-scan;
3. setiap entry dihitung SHA-256;
4. manifest dibuat;
5. manifest ditandatangani oleh `BackupSignatureService`;
6. ZIP dikirim sebagai download dan file temporary dihapus setelah response.

## Signature/authenticity lintas environment

Signature memakai:

- `BACKUP_SIGNATURE_KEY`, minimal 32 karakter;
- `BACKUP_SIGNATURE_KEY_ID`, misalnya `erp-backup-2026-01`;
- HMAC-SHA256;
- canonical JSON manifest.

Aturan:

- source dan recovery environment dalam trust group harus memakai key/key ID yang sama;
- `APP_KEY` tidak digunakan untuk backup signature;
- key tidak boleh masuk Git, log, database backup, atau ZIP;
- key/key ID berbeda membuat restore ditolak;
- kehilangan key berarti backup lama tidak dapat diverifikasi.

Lihat runbook: [Backup signature runbook](../../reviews/2026-07-11-project-baseline/09-backup-signature-runbook.md).

## Validation dan dry-run

Full restore sekarang punya dua mode:

| Mode | Efek | Kapan dipakai |
|---|---|---|
| Dry-run | validasi ZIP/signature/checksum/manifest tanpa write | sebelum recovery nyata, restore drill, cek compatibility |
| Restore | validasi lalu write database/storage sesuai opsi | maintenance window saat operator sudah siap |

UI full restore default ke dry-run. Operator harus mematikan dry-run secara sadar untuk melakukan restore destructive.

Urutan validasi sebelum write:

1. request wajib file `.zip`;
2. confirmation text wajib `RESTORE FULL BACKUP`;
3. route full restore di-throttle 3 request / 10 menit;
4. ZIP bisa dibuka oleh `ZipArchive`;
5. jumlah entry ≤ 10.000;
6. total uncompressed size ≤ 512 MiB;
7. nama entry tidak mengandung null byte, backslash, absolute path, Windows drive path, atau `..`;
8. `manifest.json` ada dan schema/version sesuai;
9. HMAC signature valid;
10. exact payload list sesuai manifest;
11. SHA-256 setiap payload valid;
12. jika dry-run, proses berhenti tanpa write;
13. jika restore database, `database.sql` dieksekusi lewat `SqlDumpExecutor`;
14. jika restore storage, `storage_public/*` dan `storage_dms_private/*` diekstrak ke root yang sudah ditentukan.

## Restore unsafe/legacy yang ditolak

Restore menolak:

- guest;
- user tanpa permission;
- settings restore tanpa selected section;
- settings restore dari JSON schema asing;
- settings restore dari manifest full backup JSON;
- full restore tanpa confirmation text;
- full restore file selain `.zip`;
- raw `.sql` atau `.txt`;
- arbitrary SQL;
- ZIP path traversal;
- ZIP entry name tidak aman;
- ZIP melewati entry/size limit;
- manifest version lama/tidak didukung;
- forged signature;
- different environment key/key ID;
- tampered database dump;
- tampered storage payload;
- exact entry list yang tidak sesuai manifest.

## DMS private storage coverage

Full-backup v3 mencakup private Document Management binary:

- source path: `storage/app/private/document-management`;
- archive prefix: `storage_dms_private/`;
- restore target: `storage/app/private/document-management`;
- checksum per entry ikut signed manifest.

Ini penting karena DMS menyimpan binary private di luar public storage. Tanpa namespace ini, metadata dokumen bisa pulih tetapi file biner hilang. Test `test_full_backup_round_trip_preserves_private_dms_binary` membuktikan round-trip binary private DMS.

## Audit behavior

Audit event yang dicatat:

- `settings.exported`;
- `settings.restored`;
- `full_backup.exported`;
- `full_backup.dry_run_validated`;
- `full_backup.restored`.

Payload audit ringkas:

- jumlah setting/template;
- basename path backup;
- database connection;
- storage size;
- summary restore;
- `dry_run` flag.

Audit tidak mencatat raw database dump, raw storage content, atau signature key.

## Frontend behavior

Halaman Backup & Restore menampilkan:

- summary system settings, encrypted settings, notification templates;
- full backup ZIP download;
- full restore form;
- dry-run checkbox default aktif;
- typed confirmation;
- warning destructive restore;
- settings backup JSON download;
- settings restore JSON form;
- included/excluded sections.

Catatan UI:

- label `Restore Storage Files` memulihkan storage public dan private Document Management;
- dry-run tetap membutuhkan permission full-restore dan confirmation text;
- jika database benar-benar direstore, user logout dan harus login ulang.

## Security review

Yang sudah baik:

- permission granular dan policy tersedia;
- full restore permission tidak diberikan default ke admin module definition;
- full restore route di-throttle;
- destructive restore memerlukan typed confirmation;
- signed ZIP menjadi satu-satunya format full restore;
- archive traversal dan zip bomb basic guard tersedia;
- exact signed payload list diverifikasi;
- private DMS binary ikut backup/restore;
- dry-run eksplisit tersedia;
- audit event tersedia.

Temuan/residual risk:

1. Restore database dan filesystem bukan satu transaksi atomic.
   - Jika database restore berhasil tetapi storage gagal, recovery bisa parsial.
   - Mitigasi operasional: maintenance window, dry-run, backup pra-restore, restore drill.
2. Full backup upload limit 512 MiB.
   - Harus diselaraskan dengan PHP/web server limit.
3. HMAC trust group bersifat symmetric.
   - Environment pemegang shared key dapat membuat backup yang dianggap valid.
   - Asymmetric signature bisa dievaluasi nanti.
4. Key rotation hanya single active key.
   - Backup lama invalid jika key diganti tanpa retention strategy.
5. Settings backup bukan disaster recovery.
   - Jangan dipakai untuk memulihkan user/role/database/storage.
6. Full database dump support aktif hanya MySQL dan SQLite.
   - Koneksi lain ditolak.

## Guide-plan koreksi lanjutan

### BR-01 — Restore drill SOP di UI

**Tujuan:** operator punya langkah ringkas sebelum destructive restore.

**Rencana:**

1. Tambahkan panel checklist: backup saat ini, maintenance mode, dry-run, restore, verifikasi login/DMS.
2. Tampilkan link ke runbook signature.
3. Tampilkan warning jika `BACKUP_SIGNATURE_KEY` belum terkonfigurasi.

### BR-02 — Multi-key verification

**Tujuan:** rotasi key tanpa langsung membuat semua backup lama tidak valid.

**Rencana:**

1. Tambahkan config list trusted old keys.
2. Manifest tetap memakai key ID.
3. Verification mencari key berdasarkan key ID.
4. Tetapkan retention dan removal date.

### BR-03 — Asymmetric signature evaluation

**Tujuan:** memisahkan signer dan verifier.

**Rencana:**

1. Evaluasi Ed25519/signature public-key.
2. Private key hanya di source/prod signer.
3. Recovery environment hanya menyimpan public key.
4. Buat ADR baru jika dipilih.

### BR-04 — Restore staging directory

**Tujuan:** mengurangi risiko partial filesystem write.

**Rencana:**

1. Extract storage ke staging directory.
2. Validasi size/hash ulang.
3. Swap/merge terkontrol.
4. Simpan manifest restore attempt.

### BR-05 — Backup size/runtime observability

**Tujuan:** operator tahu backup terlalu besar atau butuh strategi external.

**Rencana:**

1. Tampilkan size public dan private DMS di overview.
2. Warning jika mendekati upload limit.
3. Roadmap object storage/multi-server backup.

## Acceptance review

- [x] Backup format aktif terdokumentasi.
- [x] Signature/authenticity lintas environment jelas.
- [x] Restore unsafe/legacy ditolak.
- [x] DMS private storage coverage jelas.
- [x] Full restore memakai validation/dry-run yang eksplisit.

## Evidence

```bash
php artisan test --filter=BackupRestore
vendor/bin/pint --test app/Modules/Console/BackupRestores tests/Feature/BackupRestoreTest.php resources/js/pages/console/backup-restore
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

Hasil terakhir:

- `BackupRestoreTest`: 22 tests, 66 assertions, pass.
- Pint targeted: pass.
- TypeScript typecheck: pass.
- Vite build: pass.
- Module contract validation: pass.
- Diff whitespace check: pass.
