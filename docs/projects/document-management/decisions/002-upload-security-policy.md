# ADR-002: Upload security policy foundation

## Status

Accepted — 2026-07-13; ketentuan scanner/quarantine diamendemen oleh [ADR-003](003-mvp-single-server-without-malware-scanner.md).

> ADR-003 mengizinkan file structurally valid menjadi `AVAILABLE` dengan `scan_status=NOT_CONFIGURED` pada MVP. Seluruh batas ukuran, allowlist, magic-byte, polyglot rejection, private storage, dan staged cleanup pada ADR ini tetap berlaku.

## Context

Task 04 membutuhkan batas input yang deterministik sebelum binary diterima. Foundation harus mencegah oversized upload, extension/MIME spoofing, polyglot file, public storage, dan file yang dianggap aman tanpa malware scan.

Malware scanner belum dipilih pada fase ini. Karena itu, scanner tidak boleh disimulasikan atau dilewati dengan mengubah file menjadi `AVAILABLE`.

## Decision

- Maksimum ukuran satu file adalah 20 MiB (`20,971,520` byte).
- Extension yang diterima, setelah normalisasi case, hanya `pdf`, `jpg`, `jpeg`, dan `png`.
- MIME yang diterima hanya `application/pdf`, `image/jpeg`, dan `image/png`.
- Declared MIME, extension, dan detected magic-byte harus cocok dengan pasangan yang diizinkan.
- File polyglot, signature ambigu, extension ganda mencurigakan, empty stream, dan mismatch ditolak.
- Malware scanner ditunda. File yang lolos validasi belum boleh menjadi `AVAILABLE`; state maksimalnya `QUARANTINED`.
- Hasil scan gagal tetap `QUARANTINED`. Transisi `QUARANTINED -> AVAILABLE` tidak boleh dibuat sampai scanner dan prosedur operasionalnya disetujui dalam ADR lanjutan.
- Staged orphan yang tidak selesai diproses dapat direconcile setelah 24 jam. Cleanup permanent harus tetap eksplisit, bounded, dan default dry-run.
- Production foundation memakai private local disk untuk deployment single-server.
- Akses file kelak dilakukan melalui controller DMS yang terautorisasi, bukan public atau direct storage URL.

## Allowed type matrix

| Extension | Declared/detected MIME | Required signature |
|---|---|---|
| `pdf` | `application/pdf` | PDF signature |
| `jpg`, `jpeg` | `image/jpeg` | JPEG signature |
| `png` | `image/png` | PNG signature |

Magic-byte detector adalah boundary server-side. Nama file dan MIME dari client hanya input pembanding, bukan sumber kebenaran.

## Consequences

### Positive

- Task 04 dapat dibangun dan diuji secara deterministik.
- Deferral scanner tidak berubah menjadi security bypass.
- Single-server deployment memiliki storage target yang jelas.

### Negative

- Belum ada file yang dapat didownload sebagai dokumen `AVAILABLE` melalui ingestion baru.
- Task 05 harus menghasilkan `QUARANTINED`, bukan `AVAILABLE`, sampai ADR scanner diterima.
- Horizontal/multi-server deployment belum didukung oleh private-local adapter.

## Rejected alternatives

### Menganggap file bersih tanpa scanner

Ditolak karena valid extension, MIME, dan magic-byte tidak membuktikan file bebas malware.

### Public disk atau direct URL

Ditolak karena melewati authorization dan audit DMS.

### Menerima semua format office sejak awal

Ditolak karena memperluas parser/signature ambiguity dan attack surface sebelum foundation stabil.

## Follow-up gate

ADR terpisah wajib menetapkan scanner, timeout/retry, fail-open/fail-closed, signature update, observability, dan prosedur release quarantine sebelum state `AVAILABLE` diaktifkan pada production ingestion.
