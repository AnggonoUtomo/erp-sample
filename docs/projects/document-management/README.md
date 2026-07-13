# Document Management Foundation

Paket ini adalah acuan pembangunan foundation project `DocumentManagement`: satu owner untuk logical document, binary version, private storage, integrity, dan keputusan akses lintas domain. Foundation sengaja lebih kecil daripada roadmap DMS lengkap agar integrasi HR tidak mendorong pembuatan storage engine kedua.

## Status

`Specification dan ADR-001–003 accepted; Task 01–08 serta Checkpoint A–B selesai pada 2026-07-13, siap menuju Task 09`.

Persetujuan ini mengizinkan pembangunan contract dan metadata foundation. Upload production tetap tertahan sampai keputusan private disk, batas/tipe file, malware/quarantine, delivery, dan retention pada ADR-001 dipenuhi.

Foundation saat ini mengekspor contract v1, permission minimum, logical document metadata yang idempotent, private StorageAdapter, version metadata, serta endpoint ingestion/replacement internal yang dilindungi authentication, permission `documents.upload`/`documents.replace`, dan throttle. Replacement membuat version baru secara atomic; version lama tidak dioverwrite atau dihapus. Navigation dan UI belum diaktifkan.

Lifecycle archive/restore tersedia melalui permission `documents.archive` dan `documents.restore`. Archive adalah soft-delete logical document tanpa menghapus blob/version. Reader descriptor internal menghasilkan state aman `AVAILABLE|MISSING|ARCHIVED|UNAVAILABLE|DENIED`; keputusan authorization binary wajib memakai gateway Task 08.

`DocumentAccessGateway` v1 sekarang menjadi security authority sebelum binary delivery. Keputusan memerlukan actor, action, permission DMS, expected owner exact, lifecycle/version, dan private-object availability. Lihat [access decision matrix](access-decision-matrix.md); status `AVAILABLE` hanya mengizinkan proses dilanjutkan ke Task 09 dan bukan token akses.

Upload policy diterapkan pada staged ingestion: maksimal 20 MiB, PDF/JPEG/PNG, extension–declared MIME–signature wajib cocok, terminal marker wajib valid, trailing payload ditolak, dan hasil menyatakan `scanStatus=NOT_CONFIGURED`. Binary ditulis ke private staging, dipromosikan dengan object key buatan server, dan dibersihkan bila transaction/promotion gagal.

## Konfigurasi storage foundation

- Binding default memakai disk `dms-private` pada `storage/app/private/document-management`.
- Disk wajib local, `serve=false`, visibility `private`, tanpa konfigurasi URL, dan memakai exception fail-closed.
- Nama disk dapat diubah melalui `DMS_PRIVATE_DISK`, tetapi adapter akan menolak disk public/served. Driver cloud membutuhkan adapter terpisah dan keputusan arsitektur baru.
- `.env.example` menetapkan `DMS_INGESTION_ENABLED=false`. Developer dapat mengaktifkannya secara eksplisit pada environment lokal; production hanya boleh mengaktifkannya setelah checklist [Checkpoint B](checkpoint-b-ingestion-integrity.md) terpenuhi.

## Urutan baca

1. [Specification](specification.md) — objective, requirement, non-scope, contract, command, acceptance criteria, dan test plan.
2. [ADR-001: Single private storage engine](decisions/001-single-private-storage-engine.md) — ownership binary dan larangan akses path langsung.
3. [ADR-002: Upload security policy](decisions/002-upload-security-policy.md) — batas 20 MiB, allowlist PDF/JPEG/PNG, magic-byte, dan private-local production disk; bagian scanner diamendemen ADR-003.
4. [ADR-003: MVP single-server tanpa scanner](decisions/003-mvp-single-server-without-malware-scanner.md) — risk acceptance, compensating controls, dan trigger evaluasi berikutnya.
5. [Implementation plan](implementation-plan.md) — dependency graph, vertical slices, checkpoint, risiko, dan rollback.
6. [Tasks](tasks.md) — task kecil dengan tujuan, file, acceptance criteria, dependency, dan cara test.
7. [Checkpoint B: Ingestion integrity](checkpoint-b-ingestion-integrity.md) — bukti no-orphan, security review, accepted risk, dan checklist aktivasi production.
8. [Access decision matrix](access-decision-matrix.md) — urutan policy, action-permission mapping, IDOR, dan seluruh state fail-closed.
9. [Roadmap](roadmap.md) — ekspansi setelah foundation: folder, category, tag, share, approval, search, dan retention.

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
