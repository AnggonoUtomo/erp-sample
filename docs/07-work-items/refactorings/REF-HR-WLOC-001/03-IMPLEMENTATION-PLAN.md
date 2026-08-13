---
id: DOC-REF-HR-WLOC-001-IMPLEMENTATION
title: Rencana Implementasi Pilot DDD-Lite HR WorkLocations
document_type: refactoring-implementation-plan
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, ARC-DDD-LITE-001]
---

# Rencana Implementasi Pilot DDD-Lite HR WorkLocations

## Prinsip Transformasi

- Satu task aktif pada satu waktu.
- Setiap task berakhir pada repository yang dapat di-bootstrap dan diuji secara terfokus.
- Pemindahan dilakukan per concern kecil, kecuali cutover namespace model yang harus atomik karena alias dilarang.
- Tidak ada folder kosong atau abstraksi baru tanpa kebutuhan nyata.
- Perubahan perilaku, schema, permission, UI, dan kontrak tidak diperbolehkan.

## Urutan

1. Tambahkan route discovery target-first/fallback dan test tooling, tanpa memindahkan route modul.
2. Pindahkan DTO, transaction, dan service dalam task kecil terpisah sambil memperbarui consumer langsung.
3. Pindahkan model dan perbarui seluruh consumer produksi/test secara atomik tanpa alias.
4. Pindahkan request, policy, controller, dan provider per concern.
5. Pindahkan route ke Presentation/Routes/web.php; root routes.php dihapus setelah target aktif.
6. Pindahkan test milik WorkLocations dan tambahkan discovery PHPUnit secara additive.
7. Jalankan validasi fokus, regresi consumer, quality check, build, review, dan baseline sync.

Rincian task dan daftar file berada di TASKS.md.

## Aturan Kompatibilitas

- ModuleRegistry mencari Presentation/Routes/web.php terlebih dahulu dan hanya memakai routes.php jika target tidak ada.
- ModuleContractValidator menerapkan resolusi yang sama.
- Jika target dan fallback sama-sama ada, hanya target yang dipakai; tidak boleh terjadi double registration.
- Modul lain yang belum dimigrasikan tetap memakai routes.php.
- module.php diperbarui langsung ke provider target; tidak ada alias provider lama.
- Semua import WorkLocation lama diubah pada task cutover yang sama; tidak ada class_alias atau wrapper.
- PHPUnit mendapat suite/path additive untuk app/Modules; test lintas sistem tetap di tests/.

## Lokasi yang Sengaja Dipertahankan

- Database/ karena migration dan seeder adalah concern persistence yang sudah sesuai.
- module.php, navigation.php, dan permissions.php karena ADR-0001 menempatkan metadata tersebut di root modul.
- Support/Permissions.php karena discovery permission provider saat ini masih mengenali namespace tersebut. Evaluasi duplikasi diekstrak sebagai kandidat terpisah.
- resources/js/pages/hr/work-locations karena pilot tidak mengubah organisasi frontend.

## Checkpoint Wajib

| Checkpoint | Bukti minimum |
|---|---|
| Tooling kompatibel | unit test resolusi target/fallback dan module validation lulus |
| Setiap namespace cutover | bootstrap Artisan dan focused test terkait lulus |
| Route dipindahkan | snapshot enam route ekuivalen dan tidak ganda |
| Test dipindahkan | file lama tidak ada, test baru ditemukan dan lulus |
| Siap review | seluruh regression set, composer quality:check, npm build, dan git diff --check lulus |

## Rollback

Tidak ada rollback database karena tidak ada perubahan schema/data. Setiap task dapat direvert pada change set-nya. Untuk cutover model, rollback harus mengembalikan file model dan seluruh import consumer secara bersamaan. Untuk route, rollback mengembalikan routes.php dan perubahan target pada module tooling secara konsisten.

## Batas Perubahan

Daftar file rinci di TASKS.md merupakan allowlist. Penemuan file consumer baru yang wajib diubah agar bootstrap/test tetap valid boleh ditambahkan setelah dicatat pada DEVIATION-RECORD.md dan TASKS.md. Temuan yang mengubah boundary, kontrak, schema, authorization semantics, atau scope perilaku menghentikan implementasi dan dipromosikan sebagai work item baru.

## Persetujuan

Rencana disetujui Pemilik proyek pada 2026-08-14 melalui interview. Persetujuan mencakup route fallback umum, cutover namespace tanpa shim, refactor struktural murni, colocation test incremental, dan penempatan policy di Presentation.

## Rencana Sinkronisasi Dokumentasi

Setelah implementasi, dokumen berikut wajib dinilai dan hasilnya dicatat pada completion report:

| Dokumen | Ekspektasi |
|---|---|
| WORK-ITEM-REGISTRY, paket child, dan ARC-DDD-LITE-001 | status task/work item dan evidence aktual diperbarui |
| ADR-0001, SYSTEM-DESIGN, DEPENDENCY-RULES, dan acuan DDD-Lite | konfirmasi pola pilot; perubahan hanya bila implementasi menghasilkan fakta baru |
| MODULE-CATALOG | catat status struktur WorkLocations setelah verified; tanggung jawab/nama modul tidak berubah |
| TESTING-STRATEGY | snapshot module-local test diperbarui setelah discovery terbukti |
| AUTHORIZATION-MATRIX dan SECURITY-BASELINE | konfirmasi permission, Gate, allow/deny, trust boundary, dan audit tetap |
| IMPLEMENTATION-PLAN | status readiness/pilot diperbarui berdasarkan bukti |
| FILE-INVENTORY | jumlah/path dokumentasi tetap sinkron |

API-SPEC, DATABASE-DESIGN, EVENT-CATALOG, INTEGRATION-CATALOG, requirement produk, runbook, dependency register, performance baseline, dan release checklist diperkirakan `no change required` karena tidak ada perubahan perilaku, schema, kontrak, dependency, performa, atau deployment. Kesimpulan tersebut tetap harus dikonfirmasi pada review pascakerja.
