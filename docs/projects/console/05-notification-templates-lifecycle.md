# 05 — Notification Templates Lifecycle

Dokumen ini mencatat hasil telusur `Console.NotificationTemplates` seolah-olah modul ini baru akan dibuat. Fokusnya adalah lifecycle template email/notifikasi, permission update, default template, preview/render behavior, dan risiko keamanan yang perlu ditutup sebelum template dipakai lebih luas.

## Status

`Reviewed — 2026-07-19`.

Task dinyatakan selesai sebagai dokumentasi/evaluasi boundary. Ada guide-plan koreksi kecil yang sengaja tidak dikerjakan di task ini karena menyentuh keputusan behavior legacy password notification.

## Urutan baca relevan

1. [README Console](README.md) — posisi Notification Templates di project Console.
2. [Task 04 — System Settings Boundary](04-system-settings-boundary.md) — email/log delivery mode dan secret masking yang menjadi dependency pengiriman notifikasi.
3. Dokumen ini — lifecycle template notification.
4. [Tasks Console](tasks.md#task-05--notification-templates-lifecycle-) — acceptance criteria dan evidence task.
5. Nanti: Checkpoint B — Configuration and notification boundary.

## Source yang ditelusuri

- `app/Modules/Console/NotificationTemplates/module.php`
- `app/Modules/Console/NotificationTemplates/routes.php`
- `app/Modules/Console/NotificationTemplates/permissions.php`
- `app/Modules/Console/NotificationTemplates/navigation.php`
- `app/Modules/Console/NotificationTemplates/Http/Controllers/NotificationTemplateController.php`
- `app/Modules/Console/NotificationTemplates/Http/Requests/UpdateNotificationTemplateRequest.php`
- `app/Modules/Console/NotificationTemplates/Models/NotificationTemplate.php`
- `app/Modules/Console/NotificationTemplates/Policies/NotificationTemplatePolicy.php`
- `app/Modules/Console/NotificationTemplates/Providers/NotificationTemplatesServiceProvider.php`
- `app/Modules/Console/NotificationTemplates/Services/NotificationTemplateService.php`
- `resources/js/pages/console/notification-templates/index.tsx`
- `resources/js/pages/console/notification-templates/notification-template-components/template-editor-card.tsx`
- `resources/js/pages/console/notification-templates/notification-template-components/template-list-card.tsx`
- `tests/Feature/NotificationTemplateTest.php`
- `app/Modules/Console/SystemSettings/Jobs/SendUserCredentialNotificationJob.php`
- `app/Modules/Console/SystemSettings/Notifications/UserCredentialNotification.php`

## Contract modul

`Console.NotificationTemplates` adalah module Console untuk mengelola isi template notifikasi/email. Modul ini tidak mengirim email sendiri; ia hanya menyediakan template yang bisa dipakai flow lain.

Boundary utama:

- route wajib `auth`;
- view template wajib `notification-templates.view`;
- update template wajib `notification-templates.update`;
- template default dibuat otomatis oleh `NotificationTemplateService::ensureDefaults()`;
- update template mencatat audit `template.updated`;
- frontend hanya menjadi editor/list template, bukan sumber pengiriman email.

## Route dan permission

| Route | Method | Aksi | Guard |
|---|---:|---|---|
| `/notification-templates` | `GET` | list dan editor template | `auth` + policy `viewAny` |
| `/notification-templates/{notificationTemplate}` | `PUT` | update subject/body/active | `auth` + `UpdateNotificationTemplateRequest::authorize()` |

Permission yang tersedia:

- `notification-templates.view`
- `notification-templates.update`

Default role mapping saat ini:

- `admin`: view dan update;
- `staff`: tidak mendapat permission default.

## Default template

Default template dibuat dari `NotificationTemplateService::DEFAULT_TEMPLATES`.

| Key | Nama | Channel | Variable | Catatan |
|---|---|---|---|---|
| `user.activation` | Aktivasi User | `mail` | `name`, `email`, `action_url`, `app_name` | Selaras dengan flow user activation/reset link. |
| `user.credential` | Credential User | `mail` | `name`, `email`, `password`, `login_url`, `app_name` | Legacy/sensitif; lihat guide-plan koreksi. |
| `smtp.test` | Test SMTP | `mail` | `tested_at`, `app_name` | Dipakai untuk konteks test konfigurasi SMTP. |

## Lifecycle behavior

1. Saat halaman template dibuka, service memastikan default template tersedia.
2. User dengan permission view melihat daftar template.
3. User memilih template dari list.
4. User dengan permission update bisa mengubah `subject`, `body`, dan status `active`.
5. Request divalidasi:
   - `subject`: nullable string max 255;
   - `body`: nullable string max 5000;
   - `active`: required boolean.
6. Service menyimpan perubahan dan audit old/new `subject`, `body`, `active`.
7. Konsumen template dapat memanggil `render($key, $variables)`.
8. Jika template aktif, variable `{{ key }}` dan `{{key}}` diganti dari payload variable.
9. Jika template nonaktif, service mengembalikan template tanpa render variable.

## Frontend behavior

Halaman frontend memiliki dua area utama:

- `TemplateListCard` untuk memilih template dan melihat status/channel.
- `TemplateEditorCard` untuk mengedit subject, body, active state, dan melihat daftar variable yang tersedia.

Jika user tidak punya `notification-templates.update`, field editor dan tombol simpan dibuat disabled. Ini hanya UX guard; security tetap berada di backend FormRequest/policy.

## Preview dan data sensitif

Saat telusur, belum ada route/action preview yang benar-benar melakukan render sample di server. UI hanya menampilkan editor body dan daftar variable. Dengan kondisi ini:

- tidak ada aksi preview yang mengirim email;
- tidak ada preview yang melakukan dispatch job;
- tidak ada preview server-side yang memasukkan secret/PII runtime;
- risiko pengiriman secret melalui preview belum muncul karena preview executable belum ada.

Jika nanti fitur preview ditambahkan, preview harus read-only, permission-gated, memakai sample variable dummy, dan menolak variable seperti `password`, token, reset token, API key, atau secret lain.

## Security review

Yang sudah baik:

- route update protected oleh permission;
- validation boundary ada di FormRequest;
- template default tidak berasal dari input user;
- template update diaudit;
- tidak ada pengiriman email langsung dari halaman template.

Temuan yang perlu koreksi/keputusan:

1. `user.credential` masih memuat variable `password`.
2. Legacy `SendUserCredentialNotificationJob` dan `UserCredentialNotification` masih membawa plain password sebagai payload.
3. Search source tidak menemukan caller aktif untuk legacy credential job; flow User Management saat ini lebih aman karena memakai activation/reset link.
4. Audit template menyimpan body lama/baru. Ini masih aman jika body hanya berisi placeholder, tetapi admin tidak boleh memasukkan secret nyata ke isi template.
5. Copy frontend terkait inactive/fallback perlu diselaraskan dengan service: service saat ini mengembalikan template nonaktif tanpa render, bukan otomatis fallback ke default aktif.

## Guide-plan koreksi

### NT-01 — Depresiasi legacy credential password notification

**Tujuan:** menutup jalur plain-password notification agar tidak dipakai lagi tanpa sadar.

**Scope file:**

- `app/Modules/Console/SystemSettings/Jobs/SendUserCredentialNotificationJob.php`
- `app/Modules/Console/SystemSettings/Notifications/UserCredentialNotification.php`
- `app/Modules/Console/NotificationTemplates/Services/NotificationTemplateService.php`
- `tests/Feature/NotificationTemplateTest.php` atau test User Management terkait.

**Rencana:**

1. Konfirmasi apakah `user.credential` masih dibutuhkan untuk import legacy.
2. Jika tidak dibutuhkan, hapus job/notification legacy dan ubah template default menjadi inactive/deprecated atau hilangkan dari default.
3. Jika masih dibutuhkan sementara, beri guard eksplisit agar hanya environment/role tertentu yang bisa memanggil dan jangan pernah menyimpan plain password di audit/log.
4. Tambahkan test bahwa User Management tetap hanya mengirim activation/reset link.

### NT-02 — Safe preview endpoint

**Tujuan:** menyediakan preview aman tanpa mengirim email dan tanpa secret.

**Scope file:**

- `routes.php`
- controller/action preview baru jika disetujui;
- service method preview dengan dummy variables;
- frontend preview panel;
- feature test denial matrix.

**Acceptance:**

- preview hanya read-only;
- preview tidak dispatch notification/job/mail;
- preview memakai sample variable dummy;
- variable `password`, `token`, `secret`, `api_key` ditolak atau dimasking;
- user tanpa view permission ditolak.

### NT-03 — Audit body policy

**Tujuan:** memastikan template audit tidak menjadi tempat kebocoran secret.

**Rencana:**

- Tambahkan panduan UI: jangan isi template dengan secret nyata.
- Pertimbangkan audit summary/diff metadata saja untuk body panjang.
- Tambahkan test masking jika nanti template mendukung variable sensitif.

## Acceptance review

- [x] Template key/channel/body/subject terdokumentasi.
- [x] Update dibatasi permission.
- [x] Preview tidak mengirim secret/PII sensitif karena belum ada action preview/email dispatch dari halaman template.
- [x] Template default dan module permission jelas.
- [x] Gap legacy credential/password dicatat sebagai guide-plan koreksi.

## Evidence

```bash
php artisan test --filter=NotificationTemplate
vendor/bin/pint --test app/Modules/Console/NotificationTemplates tests/Feature/NotificationTemplateTest.php
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

Hasil terakhir:

- `NotificationTemplateTest`: 3 tests, 5 assertions, pass.
- Pint targeted: pass.
- TypeScript typecheck: pass.
- Vite build: pass.
- Module contract validation: pass.
- Diff whitespace check: pass.
