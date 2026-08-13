---
id: ARC-SYS-001
title: Desain Sistem Aktual dan Target DDD-Lite
document_type: architecture-baseline
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002]
---

# Desain Sistem Aktual dan Target DDD-Lite

## Konteks

Aplikasi adalah Laravel modular monolith dengan frontend React/Inertia. Kode memuat 28 manifest modul: 11 Console, 16 HR, dan 1 DocumentManagement. Angka ini adalah fakta kode saat audit 2026-08-13, bukan jumlah target setelah deprecation.

## Kondisi Aktual yang Terverifikasi

- Modul berada pada `app/Modules/{Boundary}/{Module}/`.
- Struktur internal masih dominan datar: `Models`, `Services`, `Http`, `Providers`, `routes.php`, dan folder lain berada di root modul.
- Migration modul sudah berada pada `Database/Migrations`.
- Test masih berada pada root `tests/`; belum ada test module-local.
- Route HTTP mayoritas berada pada `routes.php` masing-masing modul.
- Kontrak integrasi aktif sudah ditemukan pada beberapa modul bisnis.
- `HR/IntegrationContracts` adalah shell teknis tanpa data dan route; deprecation-nya diusulkan pada ADR-0002.
- `MakeModuleCommand.php` sempat hilang dari working tree dan telah dipulihkan exact dari commit `f1f64b2661e081ff6f2bf7418ffd9795a0ff11bd`. Test generator dan validasi kontrak modul pascarestore lulus pada 2026-08-13.

## Target Arsitektur

Target aktif mengikuti ADR-0001: lokasi baku, struktur minimal sesuai kebutuhan. DDD-Lite dipakai sekarang sebagai arah restrukturisasi, bukan label untuk keadaan kode saat ini.

```text
HTTP/CLI
  -> Presentation
  -> Application use case
  -> Domain rule bila ada / Eloquent melalui Infrastructure
  -> Database

Lintas modul sinkron -> Integration Contract milik provider
Lintas modul notifikasi -> Integration Event milik producer
Read-only lintas modul -> Query/read model terdokumentasi
```

## Boundary Aktif

| Boundary | Jumlah manifest saat ini | Tanggung jawab |
|---|---:|---|
| Console | 11 | administrasi, security operation, monitoring, dan konfigurasi |
| HR | 16 | data dan lifecycle tenaga kerja |
| DocumentManagement | 1 | ingestion, versioning, akses, dan delivery dokumen privat |

Detail dan target 27 modul tersedia pada `MODULE-CATALOG.md`.

## Kepemilikan Data

- Setiap tabel mempunyai satu modul pemilik yang dicatat pada `DATABASE-DESIGN.md`.
- Modul lain tidak mengubah model/tabel pemilik secara langsung.
- Query reporting read-only lintas tabel diperbolehkan bila eksplisit dan tidak mengambil alih aturan bisnis.
- Kontrak/event berada di modul bisnis pemilik, bukan dalam modul kontrak teknis pusat.

## Topologi Runtime

Repository membuktikan satu aplikasi Laravel dan satu unit build frontend. Dokumen ini tidak mengklaim load balancer, replica database, Redis, S3, Sentry, atau deployment production tertentu karena konfigurasi/artefak operasionalnya belum menjadi bukti baseline aktif.

## Risiko Terbuka

1. Generator lama telah pulih dan tervalidasi, tetapi masih menghasilkan struktur datar; penyesuaian ke ADR-0001 harus menjadi task terpisah.
2. Struktur target belum diimplementasikan; namespace dan route loading masih menggunakan struktur lama.
3. Beberapa dokumen lama mengklaim API, ULID, dan event yang tidak ada pada kode.
4. Dashboard/read model perlu audit terpisah untuk direct cross-module model access.

## ADR Terkait

- ADR-0001: Struktur Modul DDD-Lite yang Baku dan Adaptif.
- ADR-0002: Kepemilikan Kontrak Integrasi oleh Modul Bisnis (diusulkan).
