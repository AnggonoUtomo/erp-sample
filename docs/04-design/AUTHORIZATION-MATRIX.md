---
id: AUTH-MATRIX-001
title: Matriks Authorization Aktif
document_type: authorization-matrix
status: active
version: 1.1.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, REF-WLOC-NFR-004]
---

# Matriks Authorization Aktif

Aturan default adalah deny. Matriks ini hanya mencatat perilaku yang terverifikasi pada kode; ketiadaan suatu resource bukan berarti resource tersebut tidak dilindungi.

## HR WorkLocations

Interface aktif adalah web/session. Policy Laravel/Spatie mengadaptasi permission user ke operasi controller dan sekarang berada pada `Presentation/Policies/`; policy tersebut bukan aturan domain murni.

| Actor/kondisi | Resource | Aksi | Keputusan | Enforcement point | Audit event setelah sukses |
|---|---|---|---|---|---|
| user dengan salah satu `hr.view`, `work-locations.view`, `work-locations.manage` | WorkLocation collection | viewAny | allow | middleware `can:viewAny` dan WorkLocationPolicy | tidak ada event baca khusus |
| user dengan `work-locations.create` atau `work-locations.manage` | WorkLocation collection | create | allow | middleware `can:create`, StoreWorkLocationRequest, WorkLocationPolicy | WorkLocation.created |
| user dengan `work-locations.update` atau `work-locations.manage` | WorkLocation | update | allow | middleware `can:update` dan WorkLocationPolicy | WorkLocation.updated |
| user dengan `work-locations.delete` atau `work-locations.manage` | WorkLocation | delete/arsip | allow | middleware `can:delete` dan WorkLocationPolicy | WorkLocation.deleted |
| user dengan `work-locations.restore` atau `work-locations.manage` | WorkLocation terarsip | restore | allow | middleware `can:restore`, route binding withTrashed, dan WorkLocationPolicy | WorkLocation.restored |
| user dengan `work-locations.force-delete` atau `work-locations.manage` | WorkLocation terarsip | forceDelete | allow | middleware `can:forceDelete`, route binding withTrashed, dan WorkLocationPolicy | WorkLocation.force-deleted |
| user tanpa permission yang sesuai | WorkLocation | seluruh operasi di atas | deny/HTTP 403 | middleware/policy Laravel | tidak ada audit sukses |

Default role mapping saat ini diekspor melalui `permissions.php` dan `Support/Permissions.php`. Perbedaan cakupan role di kedua export dicatat sebagai `CAND-REF-WLOC-001`; pilot tidak mengubah hasil efektifnya.

Verifikasi `REF-HR-WLOC-001` membuktikan Gate memetakan model WorkLocation target ke WorkLocationPolicy target. Flow pengguna berizin, denial view, dan denial seluruh mutation lulus; permission key, middleware, serta role export tidak berubah.

## Batas Scope

Matriks resource lain belum direkonsiliasi oleh work item ini. Ownership-based, tenant/unit-based, state-based, serta separation-of-duty harus ditambahkan ketika bukti dan keputusan untuk resource terkait tersedia; tidak boleh diasumsikan dari bagian WorkLocations.
