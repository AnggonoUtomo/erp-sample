# ADR-003: Versioned opaque DMS reference contract

## Status

Accepted — 2026-07-13.

## Context

Employee Documents perlu mengenali availability logical document tanpa membaca model, schema, path, URL, atau storage metadata milik Document Management. DMS belum diimplementasikan pada project ini, sehingga contract harus dapat diuji tanpa membuat adapter produksi palsu yang terlihat seolah integrasi tersedia.

## Decision

Contract v1 terdiri dari:

- `EmployeeDocumentOwnerContextV1` dengan tepat `schemaVersion`, `domain`, `aggregateType`, dan `aggregateId`;
- `DocumentReferenceV1` sebagai value opaque sepanjang 1–255 karakter;
- `DocumentReferenceDescriptorV1` dengan state `AVAILABLE`, `MISSING`, `ARCHIVED`, `UNAVAILABLE`, atau `DENIED`;
- `DocumentReferenceReader::describe(reference, actorReference)` sebagai read-only boundary.

Descriptor tidak membawa path, URL, disk, media ID, signed URL, blob ID, atau detail profile employee. `actorReference` juga opaque. Fake in-memory tersedia untuk contract tests tetapi tidak di-bind di production container. Adapter nyata baru boleh ditambahkan saat project Document Management menyediakan implementation yang disetujui.

## Consequences

- HR tidak dapat menafsirkan atau membangun URL dari reference.
- `MISSING` dan `ARCHIVED` menjaga metadata HR tetapi menampilkan file unavailable.
- `UNAVAILABLE` dan `DENIED` harus fail-closed; caller tidak boleh membuat fallback storage URL.
- Perubahan field/removal membutuhkan contract version baru; penambahan backward-compatible tetap harus memperbarui schema dan tests.
- Task ini tidak mengaktifkan attach, detach, upload, preview, atau download.
