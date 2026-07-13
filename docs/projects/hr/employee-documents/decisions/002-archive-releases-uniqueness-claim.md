# ADR-002: Archive melepaskan uniqueness claim

## Status

Accepted — 2026-07-13.

## Context

Nomor dokumen aktif dilindungi oleh `document_number_uniqueness_key`. Metadata archived harus tetap menyimpan encrypted number dan fingerprint sebagai histori, tetapi tidak boleh selamanya memblokir metadata aktif pengganti. Restore juga tidak boleh menghidupkan kembali duplicate secara diam-diam.

## Decision

Archive mengosongkan `document_number_uniqueness_key` dalam transaction yang sama sebelum soft delete. Encrypted number dan fingerprint tetap dipertahankan. Restore mengunci record, memastikan employee dan document type aktif, menghitung ulang fingerprint/key dari contract tipe saat ini, menolak duplicate aktif, lalu memulihkan record dan uniqueness claim secara atomic.

Archive/restore hanya mengubah metadata HR. Keduanya tidak menghapus, memindahkan, atau memanggil file/DMS.

## Consequences

- Nomor yang sama dapat dipakai metadata aktif baru setelah record lama diarsipkan.
- Restore dapat gagal dengan validation error bila nomor sudah dipakai atau master terkait tidak aktif.
- Histori nomor tetap tersedia secara encrypted dan masked.
- Tidak ada force-delete route pada lifecycle awal.
