# Document Management Foundation

Paket ini adalah acuan pembangunan foundation project `DocumentManagement`: satu owner untuk logical document, binary version, private storage, integrity, dan keputusan akses lintas domain. Foundation sengaja lebih kecil daripada roadmap DMS lengkap agar integrasi HR tidak mendorong pembuatan storage engine kedua.

## Status

`Specification dan ADR-001 accepted pada 2026-07-13; siap memulai Task 01`.

Persetujuan ini mengizinkan pembangunan contract dan metadata foundation. Upload production tetap tertahan sampai keputusan private disk, batas/tipe file, malware/quarantine, delivery, dan retention pada ADR-001 dipenuhi.

## Urutan baca

1. [Specification](specification.md) — objective, requirement, non-scope, contract, command, acceptance criteria, dan test plan.
2. [ADR-001: Single private storage engine](decisions/001-single-private-storage-engine.md) — ownership binary dan larangan akses path langsung.
3. [Implementation plan](implementation-plan.md) — dependency graph, vertical slices, checkpoint, risiko, dan rollback.
4. [Tasks](tasks.md) — task kecil dengan tujuan, file, acceptance criteria, dependency, dan cara test.
5. [Roadmap](roadmap.md) — ekspansi setelah foundation: folder, category, tag, share, approval, search, dan retention.

## Relasi lintas dokumen

- [Employee Documents](../hr/employee-documents/README.md) — consumer pertama opaque reference, attach/detach, dan access handoff.
- [Employee Documents ADR-003](../hr/employee-documents/decisions/003-versioned-dms-reference-contract.md) — contract owner context dan failure state yang harus dipenuhi adapter.
- [Planning Document Management](../../planning/document-management.md) — peta modul besar dan submodule masa depan.
- [Data lifecycle](../../architecture/data-lifecycle.md) — soft delete, restore, dan retention boundary.
- [Project module guide](../../guides/project-module-guide.md) — aturan scaffold dan contract module.

## Asumsi yang harus dikonfirmasi sebelum coding

- Foundation berjalan di aplikasi Laravel yang sama tetapi melalui contract, bukan direct model import lintas project.
- Storage default private dan tidak web-accessible; driver production belum diputuskan.
- Database menyimpan metadata/reference, bukan binary content.
- Upload hanya aktif setelah batas ukuran, allowlist tipe, magic-byte validation, dan malware strategy disetujui.
- HR adalah consumer pertama, bukan pemilik storage atau retention.

## Output foundation

Foundation selesai ketika consumer dapat membuat logical document secara idempotent, menyimpan satu versi tervalidasi pada private storage, memperoleh opaque reference, meminta access decision, dan menerima delivery handoff tanpa mengetahui disk/path. Belum ada UI DMS lengkap pada tahap ini.
