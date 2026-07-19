# 06 — Activity Center Read Model

Dokumen ini mencatat hasil telusur `Console.ActivityCenters` seolah-olah modul ini baru akan dibuat. Fokusnya adalah memastikan Activity Center berperan sebagai read model aktivitas dari audit trail, bukan source mutation bisnis.

## Status

`Reviewed — 2026-07-19`.

Task dinyatakan selesai sebagai dokumentasi/evaluasi boundary. Tidak ada blocking issue untuk lanjut ke Audit Logs.

## Urutan baca relevan

1. [Checkpoint B — Configuration and Notification Boundary](checkpoint-b-configuration-notification-boundary.md) — checkpoint sebelum masuk observability Console.
2. Dokumen ini — read model Activity Center.
3. [Task 07 — Audit Logs immutable boundary](tasks.md#task-07--audit-logs-immutable-boundary) — source data utama Activity Center.
4. Nanti: Checkpoint C — Observability boundary.

## Source yang ditelusuri

- `app/Modules/Console/ActivityCenters/module.php`
- `app/Modules/Console/ActivityCenters/routes.php`
- `app/Modules/Console/ActivityCenters/permissions.php`
- `app/Modules/Console/ActivityCenters/Support/Permissions.php`
- `app/Modules/Console/ActivityCenters/Http/Controllers/ActivityCenterController.php`
- `app/Modules/Console/ActivityCenters/Services/ActivityCenterService.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `resources/js/components/activity-center-dropdown.tsx`
- `resources/js/components/app-menu-header.tsx`
- `resources/js/components/app-header.tsx`
- `resources/js/types/index.ts`
- `tests/Feature/ActivityCenterTest.php`

## Contract modul

`Console.ActivityCenters` adalah header/dropdown module untuk membaca ringkasan aktivitas terbaru dari `AuditLog`. Module ini tidak memiliki navigation menu sendiri.

Boundary utama:

- route wajib `auth`;
- permission utama hanya `activity-center.view`;
- summary dibagikan lewat shared Inertia props `activity_center`;
- source data berasal dari `AuditLog`;
- mutation satu-satunya adalah `markAsRead`, yaitu update marker baca user sendiri: `users.activity_center_read_at`;
- tidak boleh membuat, mengubah, atau menghapus audit/business data.

## Module export

| Export | Status | Catatan |
|---|---:|---|
| `routes` | yes | `POST activity-center/read` |
| `permissions` | yes | `activity-center.view` |
| `navigation` | no | tampil sebagai dropdown header, bukan menu sidebar |
| `events/listeners` | no | tidak publish event |
| `integrations` | no | tidak menjadi integration contract |

## Route dan permission

| Route | Method | Aksi | Guard |
|---|---:|---|---|
| `/activity-center/read` | `POST` | mark all visible activity as read untuk user login | `auth` + `activity-center.view` |

Permission yang tersedia:

- `activity-center.view`

Default role mapping saat ini:

- `admin`: mendapat `activity-center.view`;
- `staff`: tidak mendapat permission default.

## Read model behavior

`ActivityCenterService::summaryFor(User $user)` menghasilkan:

- `unread_count`;
- `read_at`;
- `items`.

Item yang ditampilkan:

- diambil dari `AuditLog::latest()->limit(8)`;
- eager load actor ringan: `id`, `name`, `email`;
- memakai timezone dan datetime format dari `SystemSettingService::localizationSettings()`;
- menghitung `unread` berdasarkan `activity_center_read_at`;
- fallback actor menjadi `System` di UI jika audit log tidak punya actor.

Shared Inertia behavior:

- jika user punya `activity-center.view`, middleware mengisi `activity_center` dari service;
- jika tidak punya permission, middleware mengirim fallback kosong:
  - `unread_count: 0`;
  - `read_at: null`;
  - `items: []`.

## Mark-as-read behavior

`markAsRead(User $user)` hanya melakukan:

```text
users.activity_center_read_at = now()
```

Maknanya:

- tidak mengubah audit log;
- tidak mengubah aktivitas milik user lain;
- tidak menghapus unread data;
- hanya menggeser marker baca milik user yang sedang login.

Route mark-as-read memakai permission yang sama dengan view. Ini masih masuk akal karena action-nya adalah personal read-state, bukan operational mutation.

## Frontend behavior

Activity Center tampil di header melalui `ActivityCenterDropdown`.

UI behavior:

- user tanpa permission tidak melihat dropdown;
- bell icon menampilkan badge unread count;
- dropdown menampilkan maksimal item yang sudah disiapkan shared props;
- tombol `Read` disabled jika unread count `0`;
- tombol `Read` memanggil `POST activity-center/read`;
- footer dropdown menyediakan shortcut ke Audit Logs.

Catatan akses link:

- Activity Center hanya mensyaratkan `activity-center.view`;
- link “Buka Audit Logs” tetap akan bergantung pada permission Audit Logs saat route Audit Logs dibuka;
- default admin biasanya mendapat keduanya, tetapi permission boundary tetap perlu dikunci pada Task 07.

## Security dan privacy review

Yang sudah baik:

- tidak ada route list publik; data dibagikan hanya lewat authenticated shared props;
- unauthorized user mendapat fallback kosong;
- mark-as-read ditolak untuk user tanpa permission;
- source data dari audit log, bukan query bebas dari input user;
- query dibatasi `limit(8)`;
- mark-as-read hanya update kolom user sendiri;
- tidak ada secret/password/token yang dibuat oleh Activity Center.

Temuan yang perlu dicatat:

1. Activity Center menampilkan `actor.email` dari audit log. Ini berguna untuk traceability, tetapi harus dievaluasi lagi saat Task 07 Audit Logs membahas PII minimization.
2. Activity Center mewarisi kualitas/masking dari Audit Logs. Jika Audit Logs menyimpan payload sensitif, Activity Center bisa ikut menampilkan deskripsi/module/event yang kurang aman.
3. Shortcut ke Audit Logs muncul di dropdown; route Audit Logs tetap harus menegakkan permission sendiri.
4. Tidak ada pagination/load-more; batas 8 item sudah aman untuk MVP header dropdown.

## Acceptance review

- [x] Activity Center tidak menjadi source mutation bisnis.
- [x] Read/mark behavior jelas.
- [x] Tidak ada navigation menu wajib karena hanya dropdown/header.
- [x] Permission boundary terdokumentasi.
- [x] Residual risk diarahkan ke Task 07 Audit Logs.

## Evidence

```bash
php artisan test --filter=ActivityCenter
vendor/bin/pint --test app/Modules/Console/ActivityCenters tests/Feature/ActivityCenterTest.php resources/js/components/activity-center-dropdown.tsx
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

Hasil terakhir:

- `ActivityCenterTest`: 3 tests, 18 assertions, pass.
- Pint targeted: pass.
- TypeScript typecheck: pass.
- Vite build: pass.
- Module contract validation: pass.
- Diff whitespace check: pass.
