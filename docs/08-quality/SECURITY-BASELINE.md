---
id: SEC-BASELINE-001
title: Baseline Keamanan Aktif
document_type: security-baseline
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [AUTH-MATRIX-001, REF-WLOC-NFR-004]
---

# Baseline Keamanan Aktif

## Aset dan Klasifikasi Data

Pilot REF-HR-WLOC-001 menyentuh data master lokasi kerja, termasuk alamat dan koordinat operasional, serta hak administratif untuk membuat, mengubah, mengarsipkan, memulihkan, dan menghapus permanen. Pilot tidak menambah kategori data, field, endpoint, atau exposure baru.

## Trust Boundary

Alur yang dipertahankan adalah request web/session terautentikasi, middleware authorization, policy Laravel/Spatie, FormRequest, service aplikasi, lalu persistence. Tidak ada public API, token authentication, integrasi eksternal, upload, atau trust boundary baru.

## Authentication dan Session

Interface aktif tetap web/session Laravel. Konfigurasi authentication, session, cookie, impersonation, dan token berada di luar scope pilot dan tidak boleh berubah.

## Model Authorization

Default adalah deny. WorkLocations memakai permission Spatie melalui WorkLocationPolicy dan middleware can. Matriks aksi, permission, allow/deny, dan audit berada di `docs/04-design/AUTHORIZATION-MATRIX.md`.

Pemindahan policy ke `Presentation/Policies/` hanya memindahkan adapter framework; permission key, role mapping, middleware, Gate mapping, serta hasil allow/deny harus tetap ekuivalen.

## Threat Ringkas Pilot

| Ancaman | Risiko perubahan struktural | Kontrol verifikasi |
|---|---|---|
| spoofing | tidak ada authentication flow baru | konfirmasi auth/session tidak berubah |
| tampering | rule request atau binding berubah saat namespace dipindah | diff review dan feature test validation/binding |
| repudiation | event audit hilang setelah service dipindah | pertahankan lima event dan assertion/bukti terfokus |
| information disclosure | response/page props melebar | no-change diff frontend dan response assertion |
| denial of service | route hilang/ganda | resolver target-first, snapshot enam route, module validation |
| elevation of privilege | Gate salah memetakan model/policy | test pengguna berizin dan tanpa permission untuk seluruh mutation |

## Secret dan Key Management

Tidak ada secret, key, credential, dependency, atau konfigurasi environment yang diubah oleh pilot.

## Audit dan Monitoring

Lima event audit WorkLocation.created, updated, deleted, restored, dan force-deleted dengan identifier modul hr.work-locations harus dipertahankan. Tidak ada logging baru.

## Pemeriksaan Development Aman

- Focused authorization test harus mempertahankan denial HTTP 403.
- Gate mapping, middleware string, permission key, dan role export dibandingkan sebelum/sesudah.
- Input validation/normalization tidak boleh berubah.
- Tidak ada migration, dependency, secret, public API, atau konfigurasi auth dalam diff.
- Temuan semantics security baru menghentikan task dan memerlukan persetujuan manusia baru.

## Risiko Diterima dan Tanggal Review

Risiko struktur campuran selama migrasi diterima untuk pilot pada 2026-08-14 dengan compatibility resolver dan test additive. Review ulang dilakukan pada review pascakerja REF-HR-WLOC-001 dan setelah pilot sebelum urutan migrasi modul berikutnya ditetapkan.
