---
id: API-SPEC-001
title: Baseline Interface HTTP Aktual
document_type: interface-specification
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002]
---

# Baseline Interface HTTP Aktual

## Status Interface

Repository saat ini tidak membuktikan adanya public REST API versioned dengan base URL `/api/v1`, bearer token Sanctum, atau response envelope generik. Klaim tersebut dihapus dari baseline aktif agar dokumentasi mengikuti perilaku kode.

Surface HTTP yang ditemukan adalah route web/session-authenticated untuk Laravel + Inertia, bersumber dari:

- `routes/web.php`, `routes/auth.php`, dan `routes/settings.php`;
- `app/Modules/Console/*/routes.php`;
- `app/Modules/HR/*/routes.php`;
- `app/Modules/DocumentManagement/Foundation/routes.php`.

## Karakteristik yang Dipertahankan Saat Restrukturisasi

1. HTTP verb, URI, route name, middleware, model binding, dan authorization tidak boleh berubah hanya karena file dipindahkan.
2. Controller target berada pada `Presentation/Http/Controllers/`.
3. Route target berada pada `Presentation/Routes/` milik modul.
4. Inertia page/component name tetap sama kecuali ada work item perilaku terpisah.

## `HR/IntegrationContracts`

Modul ini mempunyai `exports.routes = false` dan tidak memiliki file route. Endpoint berikut tidak ada pada baseline kode dan tidak boleh dianggap kontrak aktif:

- `/api/v1/integration/employees/{id}/snapshot`
- `/api/v1/integration/employees/{id}/contract-snapshot`
- `/api/v1/integration/employees/{id}/document-compliance-snapshot`

Snapshot yang ada adalah contract PHP internal dan saat audit hanya memiliki consumer langsung pada test. Transisinya diatur dalam `DEP-HR-001`.

## Keterbatasan Verifikasi

`MakeModuleCommand` yang sebelumnya hilang sudah dipulihkan dan artisan bootstrap terbukti melalui test generator serta `module:validate`. Inventaris `route:list` tidak termasuk scope task restore; sumber bukti interface HTTP dalam evaluasi ini tetap file route dan provider.

Public API baru, webhook, atau protokol eksternal memerlukan contract specification dan work item integrasi tersendiri.
