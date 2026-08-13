---
id: DOC-REF-HR-WLOC-001-DEVIATION
title: Catatan Deviasi REF-HR-WLOC-001
document_type: deviation-record
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001]
---

# Catatan Deviasi REF-HR-WLOC-001

## DEV-REF-HR-WLOC-001-001 — Struktur Campuran Sementara

Status: accepted_for_transition. ADR baru tidak diperlukan.

ADR-0001 mengizinkan migrasi incremental. Selama pilot, WorkLocations akan lebih dulu memakai lokasi target sementara modul lain tetap memakai struktur root/flat. Route discovery target-first/fallback dan PHPUnit discovery additive merupakan compatibility window. Kondisi ini diterima oleh Pemilik proyek pada 2026-08-14 dan harus ditinjau setelah pilot.

## DEV-REF-HR-WLOC-001-002 — Task Cutover Model Lebih Besar

Status: accepted. ADR baru tidak diperlukan.

Transformasi idealnya kecil, tetapi model mempunyai consumer lintas Console/HR dan test. Keputusan tanpa alias membuat pemindahan model serta seluruh import menjadi satu task atomik agar repository tidak berakhir dengan namespace setengah berpindah. Risiko dimitigasi dengan inventaris rg dan regression set.

## DEV-REF-HR-WLOC-001-003 — Perintah Audit Awal Salah

Status: resolved. ADR baru tidak diperlukan.

Audit pertama memakai option --module yang tidak tersedia pada module:validate. Perintah dikoreksi menjadi argumen posisi php artisan module:validate HR.WorkLocations --json dan lulus. Tidak ada perubahan repository atau dampak implementasi.

## DEV-REF-HR-WLOC-001-004 — Asumsi Slug Paket Induk Salah

Status: resolved. ADR baru tidak diperlukan.

Perintah inspeksi awal merujuk slug folder induk yang tidak ada. Pencarian `rg --files docs` menemukan path kanonis `docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/`, lalu seluruh inspeksi dilanjutkan dari path tersebut. Tidak ada file yang diubah oleh perintah gagal.

## DEV-REF-HR-WLOC-001-005 — Eskalasi Klasifikasi Pra-Coding

Status: resolved. ADR baru tidak diperlukan.

Klasifikasi awal SIGNIFICANT dipilih karena refactor lintas consumer. Review Human Decision Gate kemudian memastikan pemindahan policy dan wiring Gate termasuk jalur authorization. Sesuai aturan tingkat tertinggi, klasifikasi dinaikkan menjadi CRITICAL sebelum coding. Scope authorization telah disetujui Pemilik proyek dan rencana bukti security/authorization ditambahkan.

## DEV-REF-HR-WLOC-001-006 — Provider Permission Legacy Dipertahankan

Status: accepted_for_pilot. ADR baru tidak diperlukan.

Support/Permissions.php tetap berada di lokasi saat ini karena ModulePermissionRegistry masih memakainya dan isinya menambah role mapping yang tidak seluruhnya terdapat pada permissions.php. Memindahkan atau mengonsolidasikannya pada pilot akan memperluas perubahan authorization. Kondisi ini transparan sebagai residual legacy `CAND-REF-WLOC-001`; pilot hanya menyatakan concern yang berada dalam scope telah mengikuti lokasi target, bukan menyatakan seluruh legacy permission mechanism selesai.

## DEV-REF-HR-WLOC-001-007 — Perintah Copy Service Timeout

Status: resolved. ADR baru tidak diperlukan.

Percobaan Copy-Item untuk memindahkan WorkLocationsService timeout setelah 10 detik tanpa membuat file target. Pemeriksaan Test-Path memastikan target tidak ada dan sumber tetap utuh. Pemindahan kemudian dilakukan dengan apply_patch sesuai aturan edit repository.

## DEV-REF-HR-WLOC-001-008 — Pint Menerima Path Model yang Sudah Dihapus

Status: resolved. ADR baru tidak diperlukan.

Daftar otomatis dari git diff memasukkan path model sumber yang sudah dihapus sehingga Pint menolaknya sebagai tidak terbaca. Pemeriksaan diperbaiki dengan menyaring hanya file PHP yang masih ada. Ini kegagalan invokasi tooling, bukan kegagalan kode atau test.

## DEV-REF-HR-WLOC-001-009 — Assertion Awal Gate Salah Bentuk

Status: resolved. ADR baru tidak diperlukan.

Characterization test awal membandingkan hasil Gate::getPolicyFor dengan class-string, sedangkan Laravel mengembalikan instance policy. Output membuktikan instance yang benar telah terdaftar. Assertion dikoreksi menjadi assertInstanceOf tanpa perubahan kode produksi.

## DEV-REF-HR-WLOC-001-010 — Relative Migration Path Provider

Status: resolved. ADR baru tidak diperlukan.

Setelah provider dipindahkan ke Infrastructure/Providers, path relatif `__DIR__.'/../Database/Migrations'` menunjuk lokasi yang salah. Focused test gagal karena tabel hr_work_locations tidak dibuat. Path diperbarui menjadi `__DIR__.'/../../Database/Migrations'`, yaitu migration yang sama pada root modul; tidak ada migration atau schema yang berubah.

Belum ada deviasi implementasi karena coding belum dimulai.
