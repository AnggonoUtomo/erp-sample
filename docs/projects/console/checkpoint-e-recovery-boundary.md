# Checkpoint E — Recovery Boundary

Tanggal checkpoint: 2026-07-19  
Status: pass untuk dokumentasi baseline Console Task 11

Checkpoint ini mengunci hasil telusur area recovery Console: Backup & Restore. Tujuannya memastikan jalur pemulihan tidak memberi rasa aman palsu: format aktif jelas, signature/authenticity lintas environment terdokumentasi, unsafe restore ditolak, private Document Management ikut tercakup, dan destructive restore memiliki validation/dry-run eksplisit.

## Scope checkpoint

Dokumen yang menjadi input:

1. [11 — Backup Restore Signed Recovery Boundary](11-backup-restore-signed-recovery-boundary.md)
2. [Backup signature runbook](../../reviews/2026-07-11-project-baseline/09-backup-signature-runbook.md)
3. [ADR-004: Full Backup V2 Security Boundary](../../reviews/2026-07-11-project-baseline/decisions/004-full-backup-v2-security-boundary.md)
4. [ADR-005: Cross-environment Full Backup Signatures](../../reviews/2026-07-11-project-baseline/decisions/005-cross-environment-backup-signatures.md)
5. [ADR-006: Full Backup V3 mencakup private DMS](../../reviews/2026-07-11-project-baseline/decisions/006-full-backup-v3-private-dms.md)

Area source yang menjadi fokus:

- `Console.BackupRestores`;
- settings backup JSON;
- full signed ZIP v3;
- archive validation;
- HMAC signature verification;
- SHA-256 exact payload checksum;
- SQL dump execution boundary;
- storage public restore;
- private DMS storage restore;
- full restore dry-run;
- backup/restore permissions dan audit.

## Kesimpulan

Checkpoint E dinyatakan pass.

Recovery boundary sudah memenuhi baseline:

- settings backup dan full backup sudah dibedakan dengan schema berbeda;
- full backup aktif adalah signed ZIP v3;
- full backup v3 mencakup `database.sql`, `storage_public/*`, dan `storage_dms_private/*`;
- private Document Management binary ikut exact signed payload;
- full restore hanya menerima signed `.zip`;
- raw SQL/legacy/unsafe archive ditolak;
- restore memvalidasi schema, version, path safety, entry count, uncompressed size, signature, exact entry list, dan checksum sebelum write;
- dry-run full restore tersedia dan default aktif di UI;
- full restore database melakukan logout/invalidate session setelah destructive restore;
- full restore route di-throttle;
- backup/restore action permission-gated;
- audit event tersedia untuk export, dry-run, dan restore.

Tidak ada blocking correctness/security issue untuk lanjut ke Console module guide dan roadmap.

## Acceptance criteria checkpoint

- [x] Task 11 selesai.
- [x] Runbook backup sesuai behavior aktual.
- [x] Recovery path tidak memberi rasa aman palsu.

## Evidence

```bash
php artisan test --filter=BackupRestore
```

Hasil:

- 22 tests passed;
- 66 assertions.

```bash
vendor/bin/pint --test app/Modules/Console/BackupRestores tests/Feature/BackupRestoreTest.php resources/js/pages/console/backup-restore
```

Hasil:

- passed.

```bash
npm run typecheck
```

Hasil:

- TypeScript compile check lulus.

```bash
npm run build
```

Hasil:

- Vite production build lulus.

```bash
php artisan module:validate
```

Hasil:

- all module contracts are valid.

```bash
git diff --check
```

Hasil:

- pass.

## Recovery safety matrix

| Area | Status | Boundary |
|---|---|---|
| Settings backup | aman untuk config/template | schema v1, JSON valid, section terkontrol |
| Settings restore | permission-gated | `backup-restore.restore`, selected section wajib |
| Full backup | signed ZIP v3 | HMAC manifest + SHA-256 exact payload |
| Full restore upload | restricted | hanya `.zip`, confirmation text, throttle |
| Archive traversal | tertutup | reject absolute path, Windows path, `..`, backslash, null byte |
| Zip bomb dasar | mitigated | max entries dan max uncompressed bytes |
| Payload tampering | tertutup | exact entry list + checksum per entry |
| Authenticity lintas environment | tertutup jika key benar | shared `BACKUP_SIGNATURE_KEY` dan `BACKUP_SIGNATURE_KEY_ID` |
| Different key/key ID | tertutup | restore ditolak |
| Raw SQL restore | tertutup | upload `.sql` ditolak di request |
| Private DMS binary | tercakup | `storage_dms_private/*` masuk signed payload |
| Dry-run | tersedia | validasi tanpa write database/storage |
| Full DB restore session | handled | logout + invalidate session |
| Restore atomicity DB/filesystem | residual risk | bukan transaksi lintas resource; perlu SOP/drill |

## Threat model ringkas

| Threat | Status | Boundary |
|---|---|---|
| User tanpa permission membuka halaman backup | tertutup | policy `backup-restore.view` |
| User tanpa permission export settings/full backup | tertutup | policy export/full-export |
| User tanpa permission restore settings/full backup | tertutup | FormRequest/policy restore/full-restore |
| Operator salah upload manifest JSON ke settings restore | tertutup | schema check memberi pesan spesifik |
| Operator mencoba raw SQL full restore | tertutup | full restore hanya `.zip` |
| Attacker mengubah database.sql dan checksum | tertutup | manifest signature gagal |
| Attacker mengubah payload tanpa manifest | tertutup | checksum/exact payload gagal |
| Attacker memasukkan path traversal ZIP | tertutup | archive validator reject sebelum write |
| Attacker membuat archive sangat besar | mitigated | entry/size bound |
| Recovery environment beda key | tertutup | signature verification reject |
| Backup lama v1/v2 tidak mencakup DMS private | tertutup | restore v3 reject versi lama |
| Backup key bocor | residual high risk | rotasi key dan buat backup baru; future multi-key/asymmetric |
| Database restore berhasil tetapi storage gagal | residual operational risk | dry-run, maintenance window, restore drill |

## Review kualitas

### Correctness

Behavior backup/restore sesuai specification dan runbook aktif. Test mencakup akses guest/user tanpa permission, export/restore settings, full export, confirmation, legacy/raw SQL rejection, malicious ZIP, tampered payload, forged signature, cross-environment key behavior, missing signature key, private DMS binary round-trip, dry-run tanpa write, dan rate limiting.

### Maintainability

Struktur service sudah terpisah:

- `SettingsBackupService` untuk JSON settings backup;
- `FullBackupZipService` untuk ZIP create/restore;
- `FullBackupArchiveValidator` untuk archive safety;
- `BackupSignatureService` untuk signing/verifying manifest;
- `SqlDumpExecutor` untuk menjalankan dump database;
- `BackupRestoreService` sebagai orchestration dan audit boundary.

Pemisahan ini cukup sehat untuk maintenance berikutnya.

### Security

Recovery adalah area high-risk. Boundary penting sudah ada:

- permission granular;
- typed confirmation;
- throttle;
- signed backup;
- exact payload checksum;
- path traversal validation;
- dry-run;
- audit.

Residual security terbesar bukan bug langsung, melainkan key management dan operational recovery discipline.

### Performance

Full backup/restore bekerja lokal dan memakai upload limit 512 MiB. Untuk single-server MVP ini cukup, tetapi jika DMS/private storage membesar, perlu strategi backup external/object storage atau streaming.

### Consistency

Backup Restore tetap berada di Console sebagai operational foundation. DMS private storage dicakup sebagai storage namespace, bukan memindahkan ownership Document Management ke Console.

## Residual risk dan guide-plan

Tidak ada blocker sebelum lanjut ke Task 12. Follow-up yang tetap dicatat:

1. Restore drill SOP di UI.
   - Kondisi sekarang: runbook ada di docs, UI punya warning dan dry-run.
   - Guide-plan: tambahkan panel checklist operator dan link runbook.

2. Multi-key verification untuk rotasi.
   - Kondisi sekarang: satu active key/key ID.
   - Guide-plan: config trusted old keys dengan removal date.

3. Asymmetric signature evaluation.
   - Kondisi sekarang: HMAC shared-key.
   - Guide-plan: evaluasi public-key verification untuk memisahkan signer/verifier.

4. Restore staging directory.
   - Kondisi sekarang: storage diekstrak langsung setelah validasi.
   - Guide-plan: staging extraction lalu controlled swap/merge.

5. Backup size/runtime observability.
   - Kondisi sekarang: storage public size terlihat; DMS private size diaudit saat export.
   - Guide-plan: tampilkan private DMS size di overview dan warning jika mendekati limit.

6. Production restore drill.
   - Kondisi sekarang: automated tests membuktikan behavior lokal.
   - Guide-plan: lakukan drill manual di recovery environment dengan key trust group dan data realistis.

## Keputusan checkpoint

Lanjut ke Task 12 — Console module guide dan roadmap.

Alasannya: recovery boundary sudah aman sebagai MVP single-server dengan signed ZIP v3 dan dry-run eksplisit. Sisa temuan adalah peningkatan operasional/key-management, bukan blocker untuk menutup dokumentasi Console module guide dan roadmap.
