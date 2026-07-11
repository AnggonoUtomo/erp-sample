# ADR-004: Full Backup V2 Security Boundary

## Status
Accepted

## Context
Full restore menerima file yang dapat menjalankan SQL dan menulis ke public storage. Versi 1 belum membatasi archive expansion, belum memverifikasi checksum database, dan melewati entry ZIP berbahaya secara diam-diam.

## Decision
Full backup ZIP menggunakan versi 2. Restore memvalidasi schema/version, jumlah entry, total ukuran uncompressed, path traversal, dan SHA-256 `database.sql` sebelum destructive write. Raw SQL wajib memiliki header dump aplikasi. Endpoint full restore dibatasi tiga request per sepuluh menit dan tetap membutuhkan permission khusus serta confirmation text.

## Consequences
- ZIP full-backup versi 1 harus dibuat ulang sebelum dapat direstore.
- Settings backup tetap versi 1 dan tidak terpengaruh.
- Checksum mendeteksi corruption/tampering biasa, tetapi bukan authenticity signature karena checksum tersimpan di arsip yang sama.
- HMAC/signature lintas environment memerlukan keputusan key-management lanjutan.

