# ADR-005: Dual-authority secure delivery untuk Employee Documents

## Status

Accepted dan implemented — 2026-07-14.

## Context

Permission membaca metadata HR tidak otomatis memberi akses binary. Sebaliknya, permission download DMS tidak boleh dipakai untuk menebak reference milik metadata employee lain. Browser membutuhkan download tanpa HR membuat URL/path storage atau menjadi binary proxy.

## Decision

- Endpoint HR `POST .../attachment/delivery` memeriksa policy `employee-documents.view|manage` terhadap metadata yang diminta.
- HR mengirim exact opaque reference, actor, action `DOWNLOAD`, dan owner context `HR / EmployeeDocument / <metadata-id>` ke `DocumentDeliveryGateway`.
- DMS memeriksa permission `documents.download`, exact owner, lifecycle, current version, dan private object sebelum menerbitkan one-time token 300 detik.
- Browser mengonsumsi token melalui authenticated `POST /document-management/deliveries/consume`; token tidak berada pada URL, local storage, atau database HR.
- Controller HR hanya mengembalikan handoff JSON. Streaming, attachment headers, dan binary tetap ditangani controller DMS.
- Missing reference, IDOR owner mismatch, archived/unavailable DMS, expired/replayed token, serta permission yang dicabut ditolak fail-closed dengan error generik.

## Consequences

- Pengguna download membutuhkan permission HR dan DMS sekaligus.
- Frontend boleh membuat object URL sementara dari response DMS untuk memulai download dan wajib segera me-revoke URL tersebut.
- Inline preview, public/signed storage URL, dan HR binary proxy tetap non-scope.

## Verification

```bash
php artisan test --filter=EmployeeDocumentAccess
npm run typecheck
npm run build
```

Lihat [DMS ADR-004](../../../document-management/decisions/004-one-time-secure-delivery.md) dan [Employee Documents tasks](../tasks.md).
