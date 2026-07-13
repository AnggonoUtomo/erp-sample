# Document access decision matrix v1

## Tujuan

`DocumentAccessGateway` adalah authority DMS untuk keputusan akses binary. Consumer tidak boleh menyimpulkan akses hanya dari permission domain, kepemilikan reference, atau state metadata lokal.

Input v1: opaque `reference`, canonical `actorReference`, `action`, dan `expectedOwner`. Output hanya `schemaVersion`, `reference`, `action`, dan discriminated `state`; tidak ada boolean tambahan, filename, checksum, object key, path, maupun URL.

## Urutan evaluasi

1. Validasi bentuk request dan action bounded.
2. Resolve actor canonical `user:<id>` serta permission DMS sesuai action.
3. Lookup logical document dengan opaque reference.
4. Cocokkan seluruh owner context v1 secara exact.
5. Evaluasi archive/status/current version ownership dan status.
6. Periksa keberadaan private object melalui `StorageAdapter`.
7. Audit keputusan tanpa owner identifier atau storage detail.

Actor/permission diperiksa sebelum lookup dokumen agar caller tanpa otorisasi tidak dapat memakai perbedaan `MISSING`/`ARCHIVED` untuk enumerasi reference.

## Matrix

| Kondisi | State | Binary access |
|---|---|---|
| Guest atau actor null | `DENIED` | Ditolak |
| Actor tidak canonical/tidak ditemukan/soft-deleted | `DENIED` | Ditolak |
| Action selain `VIEW`/`DOWNLOAD` | `DENIED` | Ditolak |
| Hanya memiliki permission consumer, tanpa permission DMS | `DENIED` | Ditolak |
| `VIEW` tanpa `documents.view` | `DENIED` | Ditolak |
| `DOWNLOAD` tanpa `documents.download` | `DENIED` | Ditolak |
| Permission DMS telah dicabut | `DENIED` | Ditolak |
| Owner context tidak exact | `DENIED` | Ditolak sebagai IDOR |
| Authorized dan reference tidak ditemukan | `MISSING` | Ditolak |
| Authorized, owner exact, document archived | `ARCHIVED` | Ditolak |
| Metadata/version/object tidak konsisten atau storage error | `UNAVAILABLE` | Ditolak |
| Actor, action, permission, owner, lifecycle, version, dan object valid | `AVAILABLE` | Boleh dilanjutkan ke Task 09 |

`AVAILABLE` belum mengirim binary. Task 09 wajib meminta decision baru sebelum membuat delivery handoff; keputusan lama tidak boleh diperlakukan sebagai token atau capability.

## Permission mapping

| Action | Permission DMS wajib |
|---|---|
| `VIEW` | `documents.view` |
| `DOWNLOAD` | `documents.download` |

Permission seperti `employee-documents.view` hanya mengatur domain consumer dan tidak menggantikan permission DMS.

## Relasi

- [Specification](specification.md)
- [Tasks](tasks.md)
- [Checkpoint B](checkpoint-b-ingestion-integrity.md)
- [ADR-001](decisions/001-single-private-storage-engine.md)
