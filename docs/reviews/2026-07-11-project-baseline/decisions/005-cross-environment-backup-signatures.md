# ADR-005: Cross-environment Full Backup Signatures

## Status

Accepted and implemented

## Date

2026-07-12

## Context

Full-backup v2 memiliki checksum di dalam archive yang sama. Checksum membuktikan corruption, tetapi attacker yang dapat mengganti payload juga dapat mengganti checksum. Raw SQL restore juga melewati archive boundary sepenuhnya.

Environment production dan disaster recovery perlu memverifikasi backup yang berasal dari trust group yang sama tanpa bergantung pada `APP_KEY` masing-masing.

## Decision

- Gunakan HMAC-SHA256 atas canonical JSON manifest dengan secret khusus `BACKUP_SIGNATURE_KEY` minimal 32 karakter.
- Identifikasi secret aktif melalui `BACKUP_SIGNATURE_KEY_ID`.
- Manifest yang ditandatangani memuat SHA-256 setiap payload ZIP: `database.sql` dan seluruh `storage_public/*`.
- Restore memverifikasi schema/version, archive limits, signature, exact payload list, lalu checksum setiap payload sebelum SQL atau filesystem write.
- Raw `.sql`/`.txt` full restore dihapus. Hanya signed `.zip` yang diterima.
- `APP_KEY` tidak digunakan; environment yang saling mempercayai harus menerima backup signature key yang sama melalui secret manager/deployment environment.

Implementasi mengikuti PHP `hash_hmac('sha256', ...)` dan `hash_equals(...)`. Environment secret dibaca hanya melalui `config/backup.php` agar kompatibel dengan Laravel config cache.

## Alternatives

### Checksum saja

Ditolak karena tidak memiliki secret; attacker dapat mengganti payload dan checksum bersamaan.

### Menggunakan APP_KEY

Ditolak karena biasanya berbeda per environment, memiliki lifecycle berbeda, dan memperluas dampak kompromi app encryption key.

### Asymmetric signature

Ditunda. Public-key verification memisahkan signer/verifier lebih kuat, tetapi memerlukan key provisioning dan rotation design tambahan. HMAC memenuhi trust-group lintas environment saat ini.

## Consequences

- Backup unsigned v2 dan raw SQL tidak dapat direstore lagi; buat signed ZIP baru.
- Semua environment dalam trust group harus memiliki key dan key ID yang sama.
- Environment pemegang shared key dapat membuat backup yang dianggap authentic oleh anggota lain dalam trust group.
- Rotasi key langsung membuat backup lama tidak valid. Sebelum rotasi, buat recovery point baru atau pertahankan environment recovery terisolasi dengan old key sampai retention backup lama berakhir.
- Kehilangan key berarti backup terkait tidak dapat diverifikasi; key harus dibackup di secret manager, bukan repository atau archive.
