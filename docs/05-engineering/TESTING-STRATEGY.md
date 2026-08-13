---
id: ENG-TEST-001
title: Strategi Testing Aktual dan Incremental
document_type: testing-strategy
status: approved
version: 1.1.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: FTR-ENG-001
related: [ENG-TECH-001, ADR-0001, NFR-005, REF-HR-WLOC-001]
---

# Strategi Testing Aktual dan Incremental

## Tujuan

Strategi ini menetapkan cara membuktikan perubahan tanpa mengklaim tooling, lokasi test, coverage, atau quality gate yang belum tersedia. Test dipilih berdasarkan risiko dan perilaku yang berubah, bukan sekadar nama folder.

Snapshot awal berasal dari audit 2026-08-13 dan diperbarui setelah pilot WorkLocations pada 2026-08-14. Jumlah tersebut bukan target tetap.

## Topologi Test Aktual

| Area | Framework | Lokasi aktual | Snapshot |
|---|---|---|---:|
| Backend feature/integration lintas sistem/root | PHPUnit + Laravel testing helpers | tests/Feature | 100 file test |
| Backend unit/tooling | PHPUnit | tests/Unit | 6 file test |
| Frontend unit/component/hook | Vitest + React Testing Library | resources/js/**/*.test.ts(x) | 12 file, 28 test |
| Architecture | command dan test yang ada | tidak ada suite tests/Architecture | 0 file pada lokasi target |
| Module-local | PHPUnit | app/Modules/*/*/Tests | 1 file WorkLocations, 11 test |

phpunit.xml mendaftarkan suite Unit, Feature, dan Module. Suite Module memindai file `*Test.php` di bawah app/Modules secara additive. Test backend aktual memakai class PHPUnit; contoh Pest tidak menjadi konvensi aktif karena Pest tidak terpasang.

Vitest memakai environment jsdom dan setup resources/js/test/setup.ts.

## Level Testing

### Unit

Menguji aturan atau transformasi terisolasi tanpa HTTP dan tanpa integrasi eksternal. Unit test harus ditempatkan dekat ownership-nya sesuai kebijakan lokasi di bawah.

### Feature

Menguji route, middleware, authentication, authorization, request validation, controller/action, persistence, dan response sebagai satu flow Laravel. Karena interface aktif adalah web/session, contoh dan acceptance tidak boleh mengasumsikan /api/v1 atau token authentication.

### Integration dan Contract

Menguji interaksi database, binding, provider, event/listener, dan kontrak lintas modul. Label integration menjelaskan scope perilaku; saat ini sebagian bukti integration masih berada di tests/Feature atau tests/Unit.

### Architecture

Menguji manifest modul, namespace, dependency direction, dan boundary. Gate aktif yang tersedia adalah php artisan module:validate serta test repository yang benar-benar ada. PHPStan custom rules atau suite tests/Architecture merupakan rekomendasi/deferred sampai tooling dan rule-nya disetujui.

### Frontend

Menguji utility, hook, komponen, presenter, dan interaksi pengguna dengan Vitest dan React Testing Library. End-to-end browser testing belum menjadi gate repository aktif.

## Kebijakan Lokasi: Aktual dan Target

Tidak ada pemindahan massal.

- Test arsitektur, bootstrap, tooling, dan lintas sistem tetap boleh berada di tests/.
- Test milik satu modul mengikuti target ADR-0001 ke app/Modules/{Boundary}/{Module}/Tests/ secara incremental bersama slice modul yang dimigrasikan.
- Folder Feature/, Integration/, atau Unit/ di dalam modul hanya dibuat bila jenis test tersebut benar-benar dibutuhkan.
- Test lama tidak dipindahkan hanya agar struktur terlihat seragam.
- phpunit.xml, autoload, dan discovery test harus diperbarui serta diverifikasi pada work item migrasi yang benar-benar memindahkan test.

## Environment Test Aktual

phpunit.xml menetapkan environment terisolasi berikut:

| Concern | Nilai test |
|---|---|
| Database | SQLite in-memory |
| Cache | array |
| Queue | sync |
| Session | array |
| Mail | array |
| Hash rounds | 4 |

Factory dan seeder dipakai sesuai kebutuhan test yang sudah ada. Dokumen ini tidak mewajibkan setiap modul mempunyai factory atau seeder.

## Strategi Berdasarkan Jenis Perubahan

| Perubahan | Bukti minimum |
|---|---|
| Dokumentasi saja | validasi metadata/link/fence/inventory/scope; command aplikasi dipilih proporsional |
| Logic/domain | unit test terfokus dan regression/feature test untuk perilaku observabel |
| Route/controller/auth | feature test untuk happy path, validation, authentication, authorization, dan failure relevan |
| Persistence/query/migration | database test, compatibility, rollback/recovery, dan reconciliation sesuai risiko |
| Kontrak/event lintas modul | contract/integration test pada producer dan consumer terdampak |
| Frontend component/hook | Vitest/React Testing Library untuk state, interaction, loading, empty, error, dan accessibility relevan |
| Struktur modul | test terfokus, module:validate, autoload/bootstrap, serta architecture check yang tersedia |
| Dependency/config/CI | command yang menggunakan dependency/config/workflow tersebut dan review compatibility/security |

Pekerjaan CRITICAL atau perubahan authentication, authorization, data, kontrak, dan deployment memerlukan bukti tambahan sesuai work item dan Human Decision Gate.

## Perintah Aktif

    php artisan test
    composer test
    composer quality:check
    php artisan module:validate
    npm run test:frontend
    npm run lint:check
    npm run format:check
    npm run typecheck
    npm run build
    npm run quality:check

Filter test dapat dipakai bila nama test yang dituju benar-benar ada, misalnya php artisan test --filter=NamaTest. Command coverage tidak menjadi gate aktif karena PCOV/Xdebug tidak tersedia pada runner lokal saat audit dan target coverage belum disetujui.

## Snapshot Verifikasi Baseline

Hasil berikut adalah bukti work item FTR-ENG-001 pada 2026-08-13, bukan jaminan permanen:

| Command | Hasil |
|---|---|
| composer quality:check | lulus; module validation, Pint, 523 test / 3.256 assertion |
| npm run lint:check | lulus |
| npm run format:check | lulus |
| npm run typecheck | lulus |
| npm run test:frontend | lulus ketika dijalankan terisolasi; 12 file / 28 test |
| npm run build | lulus; 2.204 modul ditransformasi |

npm run quality:check pernah melewati timeout runner sekitar lima menit. Percobaan menjalankan beberapa command Node paralel juga menimbulkan tiga worker timeout pada Vitest, sementara rerun Vitest terisolasi lulus. Kejadian ini dicatat sebagai keterbatasan runner dan kandidat stabilisasi; tidak diperlakukan sebagai kegagalan test aplikasi yang dapat direproduksi.

## Snapshot Setelah Pilot WorkLocations

Hasil berikut berasal dari `REF-HR-WLOC-001` pada 2026-08-14 dan tidak menggantikan kewajiban menjalankan ulang gate pada perubahan berikutnya:

| Command | Hasil |
|---|---|
| composer quality:check | lulus; module validation, Pint, 527 test / 3.260 assertion |
| regression WorkLocations dan consumer | lulus; 41 test / 215 assertion |
| suite Module / path WorkLocations | lulus; 11 test / 29 assertion |
| composer dump-autoload --strict-psr | lulus; 7.404 class |
| npm run build | lulus; 2.204 module ditransformasi |

Pilot membuktikan colocation incremental; tidak mengharuskan pemindahan massal test lain.

## CI Aktual

| Workflow | Trigger branch | Pemeriksaan aktual |
|---|---|---|
| .github/workflows/tests.yml | develop, main | install dependency, build, module:validate, PHPUnit |
| .github/workflows/lint.yml | develop, main | Pint, ESLint, Prettier check, TypeScript |

Gap yang diketahui:

- branch aktif dev tidak sama dengan filter develop;
- Vitest tidak dijalankan pada workflow yang ada;
- setup coverage tidak sama dengan bukti coverage yang benar-benar dijalankan;
- hasil lokal tidak membuktikan workflow GitHub berhasil.

Perubahan workflow berada di luar scope FTR-ENG-001 dan harus mempunyai work item CI tersendiri.

## Coverage dan Target Numerik

Tidak ada target persentase unit, feature, integration, frontend, atau overall coverage yang aktif. Tidak ada minimum jumlah test yang boleh menggantikan penilaian terhadap perilaku dan risiko.

Coverage, performance, reliability, accessibility, dan operational threshold dipromosikan melalui work item kualitas/performa/operasional setelah baseline, cara ukur, environment, dan angka penerimaannya disetujui.

## Aturan Bukti

- Catat command persis, tanggal, exit code, ringkasan hasil, dan limitation.
- Bedakan lulus, gagal assertion, timeout runner, tool tidak tersedia, dan test yang sengaja dilewati.
- Jangan menyatakan seluruh aplikasi aman atau production-ready hanya karena suite lulus.
- Jika command agregat gagal, isolasi tahapnya sebelum menyimpulkan penyebab.
- Review dampak security, authorization, database, API/contract, deployment, dan rollback walaupun kesimpulannya no change required.
- Perubahan test mengikuti work item implementasi yang sama; pekerjaan dokumentasi ini tidak mengubah test.

## Deferred dan Rekomendasi

- static analysis PHP dan architecture rules otomatis;
- suite architecture khusus;
- end-to-end browser test;
- coverage instrumentation dan threshold;
- policy wajib untuk strict_types/final/readonly/PHPDoc/JSDoc;
- stabilisasi durasi command quality frontend;
- penyelarasan branch dan cakupan CI.

Daftar ini bukan approval, task aktif, atau quality gate.
