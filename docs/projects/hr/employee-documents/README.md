# Employee Documents

Paket dokumen ini mendefinisikan module `HR/EmployeeDocuments` sebelum implementasi dimulai. Module menyimpan metadata bisnis dokumen employee, masa berlaku, dan hasil verifikasi. File, versi, checksum, preview, download, retention, serta legal hold tetap dimiliki Document Management.

## Status

`Task 01 implemented; Task 02 menunggu instruksi`.

Document type contract telah tersedia melalui HR Reference Data dengan seed KTP, NPWP, passport, contract, certificate, medical, dan other. Metadata type divalidasi, pilihan input hanya memuat type aktif, sedangkan resolver histori tetap dapat membaca type inactive/archived.

## Urutan baca

1. [Specification](specification.md) — requirement, non-scope, data contract, route, acceptance criteria, dan test plan.
2. [ADR-001: Pisahkan metadata HR dari storage dokumen](decisions/001-hr-metadata-dms-storage-boundary.md) — ownership data dan kontrak integrasi.
3. [Implementation plan](implementation-plan.md) — urutan vertical slice, dependency, risiko, dan checkpoint.
4. [Tasks](tasks.md) — unit kerja kecil dengan tujuan, file, acceptance criteria, dependency, dan cara test.

## Relasi lintas dokumen

- [HR module guide](../module-guide.md) — pola module, permission, backend, frontend, dan quality gates.
- [Employee Contracts](../employee-contracts/README.md) — kontrak HR adalah record bisnis; attachment kontraknya nanti direferensikan melalui Employee Documents/DMS.
- [Document Management specification](../../../planning/document-management.md) — owner file, version, security download, dan retention.
- [Document Management roadmap](../../document-management/roadmap.md) — urutan implementasi storage engine lintas project.
- [Data lifecycle](../../../architecture/data-lifecycle.md) — soft delete, restore, dan larangan menghapus attachment historis secara prematur.

## Keputusan ringkas

- Employee Documents memiliki arti HR: employee, tipe dokumen, nomor, issuer, tanggal terbit/kedaluwarsa, dan verifikasi.
- Document Management memiliki file: blob, MIME, size, checksum, version, scan, preview, download, share, retention, dan legal hold.
- Integrasi menggunakan reference opaque dan contract versioned; tidak ada FK atau import model lintas project.
- Verification status dan expiry state adalah dua dimensi berbeda.
- Vertical slice pertama adalah metadata-only. Upload file menunggu contract Document Management tersedia.

## Gate sebelum coding

Implementasi baru boleh dimulai setelah manusia menyetujui:

- metadata-only sebagai vertical slice pertama;
- klasifikasi tipe dokumen memakai HR Reference Data;
- state verification dan aturan expiry;
- reference DMS nullable sampai attachment tersedia;
- retention/deletion tetap menjadi wewenang Document Management.
