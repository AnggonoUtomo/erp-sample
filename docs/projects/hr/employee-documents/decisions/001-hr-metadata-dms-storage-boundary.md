# ADR-001: Pisahkan Metadata HR dari Storage Dokumen

## Status

Accepted

## Date

2026-07-13

## Context

HR perlu memahami jenis, pemilik, masa berlaku, dan verifikasi dokumen employee. Document Management direncanakan sebagai mesin lintas project untuk blob, versi, akses, retention, dan audit file. Jika HR menyimpan file sendiri melalui media collection baru, aplikasi memiliki dua sumber storage, dua jalur download, dan dua kebijakan retention yang mudah berbeda.

Boundary juga harus tetap jelas saat Document Management belum tersedia. Metadata HR harus dapat dibangun lebih dahulu tanpa mengunci schema ke tabel internal DMS atau menghasilkan orphan file.

## Decision

`EmployeeDocuments` menjadi owner metadata bisnis HR. Document Management menjadi owner tunggal logical document, blob, version, checksum, malware scan, preview/download, sharing, retention, dan legal hold.

HR menyimpan `document_reference` opaque yang nullable dan tidak memiliki foreign key lintas project. Integrasi dilakukan melalui interface/DTO versioned. HR tidak menyimpan path, URL, disk, `media_id`, MIME, checksum, atau binary content. DMS melakukan authorization pada setiap operasi file, terpisah dari policy metadata HR.

Vertical slice pertama metadata-only. Attachment diaktifkan kemudian melalui gateway setelah contract Document Management disetujui.

## Alternatives considered

### Media Library langsung pada EmployeeDocument

- Kelebihan: upload cepat dibuat dan konsisten dengan avatar Employees saat ini.
- Kekurangan: menciptakan storage engine domain kedua, tidak memperoleh versioning/retention/legal hold DMS, dan migrasi file nanti mahal.
- Ditolak untuk dokumen HR; avatar bukan preseden untuk regulated document storage.

### Foreign key langsung ke tabel Documents

- Kelebihan: referential integrity database sederhana.
- Kekurangan: coupling schema lintas project, urutan migration rapuh, dan HR tidak dapat berjalan tanpa DMS.
- Ditolak; consistency lintas boundary dijaga adapter, reconciliation, dan contract test.

### Seluruh metadata berada di Document Management

- Kelebihan: satu tabel dokumen.
- Kekurangan: DMS harus memahami employee, verification HR, required-expiry, dan PII policy setiap domain.
- Ditolak karena metadata bisnis menjadi duplikatif dan DMS berubah menjadi god module.

### Menunda semua Employee Documents sampai DMS selesai

- Kelebihan: tidak ada temporary state.
- Kekurangan: HR tidak dapat memetakan kewajiban dokumen atau expiry lebih awal.
- Ditolak; metadata-only memberi nilai dan boundary yang dapat diuji tanpa file.

## Consequences

### Positive

- Tidak ada blob, download endpoint, atau retention engine ganda.
- HR dan DMS dapat berkembang independen melalui contract versioned.
- PII yang tidak dibutuhkan tidak bocor ke DMS.
- Metadata dapat dibuat sebelum DMS tersedia.

### Negative

- Reference tidak dapat memakai FK database; perlu reconciliation dan state `UNAVAILABLE`.
- Authorization file membutuhkan dua lapis pemeriksaan.
- Attach adalah distributed workflow dan harus menangani timeout/orphan secara eksplisit.
- UI perlu membedakan metadata tersedia dari file tersedia.

## Constraints

- Reference selalu opaque dan tidak ditafsirkan oleh HR.
- Tidak ada cascade delete lintas project.
- Archive HR tidak otomatis menghapus DMS document.
- Contract owner context memakai `schemaVersion`.
- Perubahan ownership atau reference semantics membutuhkan ADR dan versi contract baru.
- Boundary dan DMS contract v1 disetujui pada 2026-07-13; perubahan ownership/reference semantics membutuhkan ADR baru.
