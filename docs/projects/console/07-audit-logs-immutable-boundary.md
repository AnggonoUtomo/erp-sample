# 07 — Audit Logs Immutable Boundary

Dokumen ini mencatat hasil telusur `Console.AuditLogs` seolah-olah modul ini baru akan dibuat. Fokusnya adalah memastikan Audit Logs menjadi histori immutable untuk mutation penting, route user-facing bersifat read-only, dan payload audit tidak menyimpan password/token/secret.

## Status

`Reviewed and hardened — 2026-07-19`.

Task dinyatakan selesai. Ada hardening kecil pada `AuditLogService` agar redaction nilai sensitif berjalan recursive untuk old/new values.

## Urutan baca relevan

1. [06 — Activity Center Read Model](06-activity-center-read-model.md) — Activity Center membaca ringkasan dari Audit Logs.
2. Dokumen ini — source-of-truth observability mutation.
3. [Task 08 — Login Activities security observability](tasks.md#task-08--login-activities-security-observability) — observability khusus login sukses/gagal.
4. Nanti: [Checkpoint C — Observability boundary](tasks.md#checkpoint-c--observability-boundary).

## Source yang ditelusuri

- `app/Modules/Console/AuditLogs/module.php`
- `app/Modules/Console/AuditLogs/routes.php`
- `app/Modules/Console/AuditLogs/permissions.php`
- `app/Modules/Console/AuditLogs/navigation.php`
- `app/Modules/Console/AuditLogs/Models/AuditLog.php`
- `app/Modules/Console/AuditLogs/Policies/AuditLogPolicy.php`
- `app/Modules/Console/AuditLogs/Providers/AuditLogsServiceProvider.php`
- `app/Modules/Console/AuditLogs/Http/Controllers/AuditLogController.php`
- `app/Modules/Console/AuditLogs/Services/AuditLogService.php`
- `resources/js/pages/console/audit-logs/index.tsx`
- `resources/js/pages/console/audit-logs/audit-log-components/audit-log-header.tsx`
- `tests/Feature/AuditLogTest.php`
- sampling pemanggil `AuditLogService::record()` pada Console, HR, dan Document Management.

## Contract modul

`Console.AuditLogs` adalah read-only observability module untuk melihat histori mutation penting yang ditulis oleh service lain melalui `AuditLogService`.

Boundary utama:

- route user-facing hanya `GET /audit-logs`;
- view wajib `audit-logs.view`;
- tidak ada create/update/delete/restore route untuk user;
- penulisan audit hanya melalui service internal `AuditLogService::record()`;
- payload `old_values` dan `new_values` disanitasi sebelum disimpan;
- audit log menjadi source read model untuk Activity Center.

## Module export

| Export | Status | Catatan |
|---|---:|---|
| `routes` | yes | `GET audit-logs` |
| `permissions` | yes | `audit-logs.view` |
| `navigation` | yes | menu Observability → Audit Logs |
| `events/listeners` | no | audit ditulis langsung via service internal |
| `integrations` | no | bukan integration contract eksternal |

## Route dan permission

| Route | Method | Aksi | Guard |
|---|---:|---|---|
| `/audit-logs` | `GET` | list/filter/detail audit logs | `auth` + policy `viewAny` |

Permission:

- `audit-logs.view`

Default role mapping:

- `admin`: mendapat `audit-logs.view`;
- `staff`: tidak mendapat permission default.

## Data model

`AuditLog` menyimpan:

- `actor_id`;
- `module`;
- `event`;
- `auditable_type`;
- `auditable_id`;
- `description`;
- `old_values`;
- `new_values`;
- `ip_address`;
- `user_agent`;
- timestamps.

Relasi:

- `actor()` → `User`;
- `auditable()` → polymorphic target.

`old_values` dan `new_values` dicast menjadi array.

## List/filter behavior

Controller `AuditLogController@index`:

1. authorize `viewAny` terhadap `AuditLog`;
2. membaca filter:
   - `search`;
   - `module`;
   - `event`;
   - `per_page`;
3. query `AuditLog` dengan eager load `actor:id,name,email`;
4. search mencakup:
   - `description`;
   - `event`;
   - `module`;
   - actor `name`;
   - actor `email`;
5. filter module/event memakai nilai exact;
6. sorting `latest()`;
7. pagination memakai `SystemSettingService::resolvePerPage()`;
8. data ditransform untuk Inertia.

Frontend:

- tabel list audit logs;
- filter search/module/event;
- detail panel menampilkan actor, request context, old values, new values;
- pagination memakai shared `PaginationBar`.

## Write behavior internal

Service `AuditLogService::record()` menerima:

- `module`;
- `event`;
- optional `auditable`;
- optional `description`;
- optional `oldValues`;
- optional `newValues`;
- optional `actor`;
- fallback actor dari authenticated user;
- optional `throwOnFailure`.

Behavior:

- jika bukan console command, service mengambil `ip_address` dan `user_agent` dari request;
- jika actor tidak diberikan dan fallback aktif, actor memakai `Auth::user()`;
- jika audit write gagal, default-nya tidak mematikan business transaction; error direport;
- caller bisa memilih `throwOnFailure: true` untuk konteks yang ingin fail-fast.

## Hardening yang diterapkan

Sebelum task ini, sanitizer hanya membuang beberapa key sensitif top-level. Pada task ini sanitizer diperkuat:

- recursive ke nested array;
- redaction mempertahankan key tetapi mengganti nilai menjadi `[redacted]`;
- key sensitif dikenali dengan pola:
  - `password`;
  - `token`;
  - `secret`;
  - `api_key`;
  - `apikey`;
- berlaku untuk `old_values` dan `new_values`.

Contoh:

```json
{
  "email": "safe@example.com",
  "password": "[redacted]",
  "reset_token": "[redacted]",
  "nested": {
    "smtp_password": "[redacted]",
    "google_maps_api_key": "[redacted]",
    "visible": "safe-value"
  }
}
```

Keputusan ini menjaga audit tetap berguna tanpa menyimpan rahasia plaintext.

## Immutable boundary

Audit Logs immutable dari sisi user karena:

- tidak ada route create/update/delete;
- tidak ada controller action mutation terhadap `AuditLog`;
- policy hanya punya `viewAny`;
- frontend hanya list/filter/detail;
- Activity Center hanya membaca audit log dan tidak mengubah audit row.

Catatan: immutable di sini adalah application-level immutable. Database masih dapat diubah oleh administrator database atau restore process. Retention, archival, dan tamper-evident log belum diformalkan pada MVP ini.

## Security dan privacy review

Yang sudah baik:

- route view protected oleh permission;
- unauthorized user ditolak;
- tidak ada mutation route audit log untuk user;
- old/new values disanitasi sebelum persist;
- query list dipaginate;
- Activity Center hanya menampilkan subset ringan dari audit log;
- secret email/map/maintenance dari System Settings sudah masked sebelum audit, lalu tetap dilindungi sanitizer.

Temuan/residual risk:

1. Actor email ditampilkan di Audit Logs dan Activity Center.
   - Ini berguna untuk traceability.
   - Tetap PII; perlu policy retention/minimization nanti.
2. `ip_address` dan `user_agent` disimpan.
   - Berguna untuk investigasi.
   - Perlu retention policy untuk produksi.
3. `description` ditulis oleh caller.
   - Caller harus tidak memasukkan password/token/secret ke description.
   - Sanitizer saat ini hanya berlaku untuk old/new values, bukan description.
4. `old_values`/`new_values` masih dapat memuat data personal non-secret seperti email, nama, employee number, status, dan reason.
   - Ini diterima untuk audit MVP, tetapi perlu retensi dan access control yang ketat.
5. Tamper-evident chain/signature audit log belum ada.
   - Tidak wajib MVP, tetapi bisa menjadi roadmap compliance.

## Guide-plan koreksi lanjutan

### AL-01 — Audit retention dan PII minimization

**Tujuan:** menentukan berapa lama audit log disimpan dan field mana yang boleh tampil.

**Rencana:**

1. Tentukan retention default, misalnya 180/365 hari.
2. Tentukan apakah actor email boleh tampil untuk role tertentu saja.
3. Tentukan apakah IP/user agent perlu masking parsial.
4. Buat command archive/prune jika sudah disetujui.

### AL-02 — Description safety guideline

**Tujuan:** mencegah caller menulis secret ke field `description`.

**Rencana:**

1. Tambahkan guideline di module guide.
2. Pertimbangkan sanitizer sederhana untuk description jika menemukan pola `password=`, `token=`, `secret=`.
3. Review pemanggil audit baru di code review checklist.

### AL-03 — Tamper-evident audit chain

**Tujuan:** membuat audit log lebih kuat untuk compliance.

**Rencana:**

1. Evaluasi kebutuhan setelah MVP.
2. Jika dibutuhkan, tambah hash chain per row atau signed audit export.
3. Jangan dilakukan sebelum backup/restore dan storage policy benar-benar stabil.

## Acceptance review

- [x] Audit log read-only untuk user.
- [x] Audit service tidak menyimpan password/token/secret plaintext pada `old_values`/`new_values`.
- [x] Filter/list behavior terdokumentasi.
- [x] Retention/archival gap dicatat.
- [x] Relasi dengan Activity Center jelas.

## Evidence

```bash
php artisan test --filter=AuditLog
vendor/bin/pint --test app/Modules/Console/AuditLogs tests/Feature/AuditLogTest.php resources/js/pages/console/audit-logs/index.tsx
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

Hasil terakhir:

- `AuditLogTest`: 4 tests, 10 assertions, pass.
- Pint targeted: pass.
- TypeScript typecheck: pass.
- Vite build: pass.
- Module contract validation: pass.
- Diff whitespace check: pass.
