---
id: DEP-RULES-001
title: Aturan Dependensi DDD-Lite
document_type: architecture-standard
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002]
---

# Aturan Dependensi DDD-Lite

## Arah di Dalam Modul

```text
Presentation -> Application -> Domain
       |              ^          ^
       `--------------|----------|
Infrastructure -------'----------'

Integration = surface lintas modul yang dimiliki modul bisnis
```

- `Presentation` menangani HTTP/CLI dan memanggil use case Application.
- `Application` mengorkestrasi use case dan transaksi; tidak bergantung pada Request/Response.
- `Domain` hanya hadir bila terdapat invariant atau konsep bisnis nyata dan tidak bergantung pada Laravel HTTP/Presentation.
- `Infrastructure` berisi Eloquent, binding, storage, dan adapter teknis.
- `Integration` berisi contract/DTO/event versioned lintas modul, bukan service internal umum.

Folder yang tidak diperlukan tidak dibuat.

## Aturan Lintas Modul

| ID | Sumber | Target yang diizinkan | Akses yang dilarang | Penegakan target |
|---|---|---|---|---|
| `DEP-001` | Modul apa pun | `Integration/Contracts`, `Integration/DTOs`, `Integration/Events` milik provider | Mengubah Eloquent model/tabel modul lain langsung | architecture test + review |
| `DEP-002` | Reporting/dashboard | Query read-only yang terdokumentasi | Mutation atau penyelundupan aturan bisnis melalui join | review + focused test |
| `DEP-003` | Presentation | Application action/query modul yang sama | Business orchestration di controller | architecture test + review |
| `DEP-004` | Domain | Domain sendiri dan primitive/shared concept stabil | Laravel HTTP, Inertia, Controller, Request | static check + review |
| `DEP-005` | Shared/root integration | Konsep/mekanisme generik dengan minimal dua consumer nyata | Aturan bisnis satu modul atau contract tanpa owner | impact review |

## Kepemilikan Kontrak

1. Provider bisnis memiliki contract sinkron dan DTO hasilnya.
2. Producer bisnis memiliki event dan schema event-nya.
3. Consumer memiliki adapter yang menerjemahkan contract provider ke use case consumer bila diperlukan.
4. Registry dokumentasi berada pada katalog SEOS; registry runtime hanya dibuat jika ada kebutuhan eksekusi nyata.
5. `HR/IntegrationContracts` tidak menjadi pola target. Transisinya dikelola oleh `DEP-HR-001`.

## Kondisi Aktual

Kode belum dinyatakan patuh terhadap seluruh aturan ini. Struktur masih datar dan beberapa query lintas modul masih menggunakan model langsung. Setiap deviasi harus diinventarisasi pada irisan migrasi terkait; dokumen ini tidak mengubah perilaku kode secara otomatis.
