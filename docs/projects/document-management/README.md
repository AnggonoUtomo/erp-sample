# Document Management Foundation

Paket ini adalah acuan pembangunan foundation project `DocumentManagement`: satu owner untuk logical document, binary version, private storage, integrity, dan keputusan akses lintas domain. Foundation sengaja lebih kecil daripada roadmap DMS lengkap agar integrasi HR tidak mendorong pembuatan storage engine kedua.

## Status

`Specification dan ADR-001–003 accepted; Task 01–05 serta Checkpoint A selesai pada 2026-07-13, siap menuju Task 06`.

Persetujuan ini mengizinkan pembangunan contract dan metadata foundation. Upload production tetap tertahan sampai keputusan private disk, batas/tipe file, malware/quarantine, delivery, dan retention pada ADR-001 dipenuhi.

Foundation saat ini mengekspor contract v1, permission minimum, logical document metadata yang idempotent, private StorageAdapter, version metadata, dan endpoint ingestion internal yang dilindungi authentication, permission `documents.upload`, serta throttle. Navigation dan UI belum diaktifkan.

Upload policy diterapkan pada staged ingestion: maksimal 20 MiB, PDF/JPEG/PNG, extension–declared MIME–signature wajib cocok, terminal marker wajib valid, trailing payload ditolak, dan hasil menyatakan `scanStatus=NOT_CONFIGURED`. Binary ditulis ke private staging, dipromosikan dengan object key buatan server, dan dibersihkan bila transaction/promotion gagal.

## Konfigurasi storage foundation

- Binding default memakai disk `dms-private` pada `storage/app/private/document-management`.
- Disk wajib local, `serve=false`, visibility `private`, tanpa konfigurasi URL, dan memakai exception fail-closed.
- Nama disk dapat diubah melalui `DMS_PRIVATE_DISK`, tetapi adapter akan menolak disk public/served. Driver cloud membutuhkan adapter terpisah dan keputusan arsitektur baru.

## Urutan baca

1. [Specification](specification.md) — objective, requirement, non-scope, contract, command, acceptance criteria, dan test plan.
2. [ADR-001: Single private storage engine](decisions/001-single-private-storage-engine.md) — ownership binary dan larangan akses path langsung.
3. [ADR-002: Upload security policy](decisions/002-upload-security-policy.md) — batas 20 MiB, allowlist PDF/JPEG/PNG, magic-byte, dan private-local production disk; bagian scanner diamendemen ADR-003.
4. [ADR-003: MVP single-server tanpa scanner](decisions/003-mvp-single-server-without-malware-scanner.md) — risk acceptance, compensating controls, dan trigger evaluasi berikutnya.
5. [Implementation plan](implementation-plan.md) — dependency graph, vertical slices, checkpoint, risiko, dan rollback.
6. [Tasks](tasks.md) — task kecil dengan tujuan, file, acceptance criteria, dependency, dan cara test.
7. [Roadmap](roadmap.md) — ekspansi setelah foundation: folder, category, tag, share, approval, search, dan retention.

## Relasi lintas dokumen

- [Employee Documents](../hr/employee-documents/README.md) — consumer pertama opaque reference, attach/detach, dan access handoff.
- [Employee Documents ADR-003](../hr/employee-documents/decisions/003-versioned-dms-reference-contract.md) — contract owner context dan failure state yang harus dipenuhi adapter.
- [Planning Document Management](../../planning/document-management.md) — peta modul besar dan submodule masa depan.
- [Data lifecycle](../../architecture/data-lifecycle.md) — soft delete, restore, dan retention boundary.
- [Project module guide](../../guides/project-module-guide.md) — aturan scaffold dan contract module.

## Asumsi yang harus dikonfirmasi sebelum coding

- Foundation berjalan di aplikasi Laravel yang sama tetapi melalui contract, bukan direct model import lintas project.
- Storage production foundation memakai private local disk untuk deployment single-server.
- Database menyimpan metadata/reference, bukan binary content.
- Upload policy mengikuti ADR-002/ADR-003; MVP tanpa scanner boleh menghasilkan `AVAILABLE` dengan `scan_status=NOT_CONFIGURED` dan compensating controls wajib.
- HR adalah consumer pertama, bukan pemilik storage atau retention.

## Output foundation

Foundation selesai ketika consumer dapat membuat logical document secara idempotent, menyimpan satu versi tervalidasi pada private storage, memperoleh opaque reference, meminta access decision, dan menerima delivery handoff tanpa mengetahui disk/path. Belum ada UI DMS lengkap pada tahap ini.
