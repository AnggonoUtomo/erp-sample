# Checkpoint B — Configuration and Notification Boundary

Tanggal checkpoint: 2026-07-19  
Status: pass untuk dokumentasi baseline Console Task 04–05

Checkpoint ini mengunci hasil telusur area konfigurasi dan notification template Console. Tujuannya memastikan System Settings dan Notification Templates sudah punya boundary permission, masking secret, delivery mode, audit, dan guide-plan koreksi yang jelas sebelum lanjut ke observability module seperti Activity Center, Audit Logs, dan Login Activities.

## Scope checkpoint

Dokumen yang menjadi input:

1. [04 — System Settings Configuration Boundary](04-system-settings-boundary.md)
2. [05 — Notification Templates Lifecycle](05-notification-templates-lifecycle.md)

Area source yang menjadi fokus:

- `Console.SystemSettings`;
- `Console.NotificationTemplates`;
- profile settings yang terkait delete account visibility;
- email/log delivery setting;
- Google Maps API key runtime boundary;
- maintenance mode secret/bypass URL;
- notification template default dan render behavior;
- permission denial matrix untuk settings/template mutation;
- audit payload untuk konfigurasi dan template.

## Kesimpulan

Checkpoint B dinyatakan pass.

Configuration dan notification boundary sudah memenuhi baseline:

- System Settings memakai route `auth`, policy `system-settings.view/update`, FormRequest authorization, service boundary, DTO/props, dan audit;
- email delivery mode dapat diarahkan ke local/log untuk development/UAT tanpa mengirim email eksternal;
- email password, Google Maps API key, dan maintenance secret tidak dikirim plaintext ke frontend System Settings;
- Google Maps API key hanya dibuka lewat method runtime eksplisit untuk consumer map;
- maintenance secret disimpan encrypted, masked di props/audit, dan update kosong mempertahankan secret lama;
- delete account visibility dikontrol dari System Settings dan ditegakkan di profile destroy action;
- Notification Templates memakai permission `notification-templates.view/update`;
- default template dibuat deterministik oleh service;
- halaman template tidak mengirim email dan belum punya executable preview route, sehingga tidak ada jalur preview yang mengirim secret/PII;
- mutation penting dicatat ke audit service;
- tidak ada blocking correctness/security issue untuk lanjut ke observability boundary.

## Acceptance criteria checkpoint

- [x] Task 04–05 selesai.
- [x] Secret masking/encryption policy jelas.
- [x] Email/log delivery mode jelas.
- [x] Mutation settings/templates permission-gated.

## Evidence

```bash
php artisan test --filter="SystemSetting|NotificationTemplate|ProfileUpdate"
```

Hasil:

- 26 tests passed;
- 155 assertions.

```bash
vendor/bin/pint --test app/Modules/Console/SystemSettings app/Modules/Console/NotificationTemplates tests/Feature/SystemSettingTest.php tests/Feature/NotificationTemplateTest.php tests/Feature/Settings/ProfileUpdateTest.php
```

Hasil:

- passed.

```bash
php artisan module:validate
```

Hasil:

- all module contracts are valid.

```bash
npm run typecheck
```

Hasil:

- TypeScript compile check lulus.

```bash
npm run build
```

Hasil:

- Vite production build lulus.

```bash
git diff --check
```

Hasil:

- pass.

## Threat model ringkas

| Threat | Status | Boundary |
|---|---|---|
| User tanpa permission melihat settings | tertutup | policy `system-settings.view` |
| User tanpa permission mutate settings | tertutup | policy/request `system-settings.update` dan tests |
| Email password bocor ke frontend | tertutup | password tidak dikirim balik; hanya status configured |
| Map API key bocor dari halaman System Settings | tertutup | props masked/null; runtime map memakai method eksplisit |
| Maintenance secret bocor ke frontend/audit | tertutup | encrypted at rest, props/audit masked |
| Update kosong menghapus secret existing | tertutup | blank update mempertahankan secret lama |
| Delete account tetap muncul saat disabled | tertutup | setting dibaca profile props dan destroy abort 403 |
| User tanpa permission update notification template | tertutup | FormRequest authorize `notification-templates.update` |
| Preview template mengirim email/secret | tidak ada jalur aktif | belum ada executable preview route; halaman hanya editor/list |
| Legacy plain password notification aktif tanpa sadar | risiko residual | legacy class/template tercatat untuk depresiasi/guard |

## Residual risk dan guide-plan

Tidak ada blocker sebelum lanjut ke Task 06. Follow-up yang tetap dicatat:

1. Depresiasi atau guard legacy credential password notification.
   - Kondisi sekarang: `user.credential` default template dan legacy `SendUserCredentialNotificationJob`/`UserCredentialNotification` masih ada, tetapi tidak ditemukan caller aktif pada flow User Management saat ini.
   - Guide-plan: konfirmasi kebutuhan legacy; jika tidak dipakai, hapus/deprecated template dan class legacy; jika masih perlu, beri guard eksplisit dan test agar plain password tidak masuk audit/log.

2. Safe preview endpoint untuk Notification Templates.
   - Kondisi sekarang: UI belum punya preview server-side yang eksplisit.
   - Guide-plan: jika preview dibutuhkan, buat endpoint read-only, permission-gated, tidak dispatch email/job, memakai dummy variables, dan menolak/masking variable sensitif seperti `password`, `token`, `secret`, `api_key`.

3. Audit body policy untuk template.
   - Kondisi sekarang: audit template menyimpan `subject`, `body`, dan `active` old/new.
   - Guide-plan: tambahkan panduan bahwa admin tidak boleh menulis secret nyata di template; pertimbangkan audit summary untuk body panjang/sensitif.

4. Security policy enforcement settings.
   - Kondisi sekarang: beberapa setting seperti single session dan require email verification terdokumentasi, tetapi enforcement penuh belum ditelusuri pada Task 04.
   - Guide-plan: telusuri bersama Login Activities/security observability atau task hardening Console berikutnya.

5. Copy behavior inactive template.
   - Kondisi sekarang: service mengembalikan template nonaktif tanpa render variable; beberapa copy UI bisa ditafsirkan sebagai fallback default.
   - Guide-plan: samakan copy UI dengan behavior aktual atau ubah service jika ingin fallback eksplisit.

## Keputusan checkpoint

Lanjut ke Task 06 — Activity Center read model.

Alasannya: konfigurasi runtime, masking secret, email/log mode, delete account visibility, dan notification template update boundary sudah punya evidence yang cukup. Sisa temuan adalah koreksi incremental yang bisa dikerjakan setelah observability boundary mulai ditelusuri, terutama karena Activity Center/Audit Logs akan membantu memvalidasi apakah mutation settings/templates tercatat dengan payload yang aman.
