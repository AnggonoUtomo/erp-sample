# 09 — Backup Signature Runbook

## Provisioning lintas environment

1. Generate secret 32-byte atau lebih pada workstation/secret manager tepercaya:

    `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`

2. Simpan nilai yang sama sebagai `BACKUP_SIGNATURE_KEY` pada source dan recovery environment.
3. Tetapkan `BACKUP_SIGNATURE_KEY_ID` yang sama, misalnya `erp-backup-2026-01`.
4. Jalankan `php artisan config:clear` atau rebuild `php artisan config:cache` setelah deployment.
5. Jangan menyimpan key di Git, log, database backup, atau file ZIP.

## Verifikasi recovery

1. Buat full backup ZIP melalui aplikasi source.
2. Salin ZIP ke recovery environment melalui channel terkontrol.
3. Pastikan recovery environment memakai key/key ID trust group yang sama.
4. Jalankan restore dengan permission `backup-restore.full-restore` dan confirmation text.
5. Signature, exact entry list, dan seluruh checksum diverifikasi sebelum write.

Expected failure:

- Key/key ID berbeda: restore ditolak.
- Manifest atau payload berubah: restore ditolak.
- Raw SQL/unsigned ZIP: restore ditolak.
- Key tidak dikonfigurasi/minimal 32 karakter: export/restore gagal dengan configuration error.

## Rotasi

1. Tentukan retention boundary backup dengan key lama.
2. Buat dan uji recovery point terakhir sebelum rotasi.
3. Distribusikan key baru dan key ID baru ke seluruh trust group dalam deployment window yang sama.
4. Rebuild config cache.
5. Buat signed backup baru dan lakukan restore drill.
6. Hapus old key hanya setelah backup lama melewati retention atau tidak lagi dibutuhkan.

Implementasi saat ini memakai satu active key. Multi-key verification harus dibuat sebagai perubahan contract baru bila overlapping rotation diperlukan.
