# Modul Aplikasi

Direktori ini berisi modul pada tiga boundary aktif: Console, HR, dan DocumentManagement.

## Sumber Kebenaran Aktif

- Struktur target: `docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md`.
- Katalog modul: `docs/03-architecture/MODULE-CATALOG.md`.
- Boundary: `docs/03-architecture/BOUNDARY-REGISTRY.md`.
- Dependensi: `docs/03-architecture/DEPENDENCY-RULES.md`.
- Integrasi/event: `docs/04-design/INTEGRATION-CATALOG.md` dan `docs/04-design/EVENT-CATALOG.md`.
- Work item aktif: `docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/`.

## Kondisi Saat Ini

Kode masih dominan menggunakan struktur modul datar. Jangan memindahkan file secara ad-hoc. Setiap slice mengikuti work item SEOS, satu task aktif, context pack, test terfokus, dan evidence manifest.

Target DDD-Lite menggunakan lokasi baku dengan struktur minimal. `Domain/` hanya dibuat untuk aturan domain nyata. `Integration/` hanya dibuat ketika modul mempunyai contract, DTO, adapter, listener, atau event lintas modul.

`HR/IntegrationContracts` sedang dievaluasi untuk deprecation melalui `DEP-HR-001`; tidak boleh dihapus atau dipindahkan sebelum readiness gate disetujui.

## Riwayat

Referensi dokumentasi project lama yang sebelumnya ditautkan dari file ini berada pada commit `aab3c87a88ccdda64c95051ec72b431648e0ecdf` dan dicatat oleh `docs/11-baselines/BL-2026-001-pre-seos/`. Dokumen tersebut adalah bukti historis, bukan baseline aktif.
