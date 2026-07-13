# ADR-004: One-time secure delivery melalui controller DMS

## Status

Accepted dan implemented — 2026-07-14.

## Context

Binary berada pada private-local storage single-server. Consumer membutuhkan file tanpa menerima path, object key, credential, atau URL storage. Task 09 juga harus tetap memvalidasi izin setelah handoff diterbitkan agar pencabutan akses langsung berlaku.

## Decision

- DMS menerbitkan opaque token acak 256-bit yang terikat pada actor, reference, current version, owner context, dan action `DOWNLOAD`.
- TTL MVP adalah 300 detik dan konfigurasi hanya boleh berada pada rentang aman 30–900 detik.
- Database DMS hanya menyimpan HMAC-SHA256 token dan owner fingerprint; raw token hanya muncul sekali pada response handoff.
- Consume dilakukan dengan `POST` body ke controller DMS, bukan query string atau path, lalu token ditandai terpakai secara atomik.
- Consume mengulang keputusan akses DMS. Expiry, revoke, replay, actor/action/owner berbeda, permission dicabut, document berubah, atau version bukan current ditolak dengan respons seragam.
- Controller selalu mengirim `Content-Disposition: attachment`, `Cache-Control: private, no-store`, `X-Content-Type-Options: nosniff`, dan CSP `sandbox`.
- Audit hanya menyimpan reference, action, actor, expiry, dan lifecycle event; token, owner fingerprint, storage object key, URL, dan isi file dilarang.

## Consequences dan batasan

- Tidak ada presigned URL, inline preview, range request, resume download, CDN, atau public sharing pada MVP.
- Streaming memakai proses aplikasi dan private-local disk; kapasitas concurrent download harus dievaluasi sebelum skala multi-server.
- Consumer boleh memakai `DocumentDeliveryGateway`, tetapi tidak boleh menyimpan raw token. Handoff harus segera diteruskan ke client yang berhak.
- Migrasi ke object storage/presigned delivery memerlukan ADR baru, TTL/action scope yang setara, dan pengujian kebocoran URL/credential.

## Verification

```bash
php artisan test --filter=DocumentDelivery
php artisan module:validate
```

Lihat juga [access decision matrix](../access-decision-matrix.md), [specification](../specification.md), dan [Task 09](../tasks.md).
