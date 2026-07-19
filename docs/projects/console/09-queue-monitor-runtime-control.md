# 09 — Queue Monitor Runtime Control

Dokumen ini mencatat hasil telusur `Console.QueueMonitors` seolah-olah modul ini baru akan dibuat. Fokusnya adalah memastikan Queue Monitor menjadi alat operasi runtime yang permission-gated, memisahkan view dan manage action, serta tidak mengekspos payload job mentah yang berpotensi membawa data sensitif.

## Status

`Reviewed and hardened — 2026-07-19`.

Task dinyatakan selesai. Ada hardening kecil pada `QueueMonitorService` agar exception summary failed job me-redact pola secret sebelum ditampilkan ke frontend.

## Urutan baca relevan

1. [Checkpoint C — Observability Boundary](checkpoint-c-observability-boundary.md) — checkpoint sebelum masuk runtime operation.
2. Dokumen ini — boundary Queue Monitor.
3. [Task 10 — Scheduler Monitor runtime control](tasks.md#task-10--scheduler-monitor-runtime-control) — runtime operation berikutnya.
4. Nanti: [Checkpoint D — Runtime operation boundary](tasks.md#checkpoint-d--runtime-operation-boundary).

## Source yang ditelusuri

- `app/Modules/Console/QueueMonitors/module.php`
- `app/Modules/Console/QueueMonitors/routes.php`
- `app/Modules/Console/QueueMonitors/permissions.php`
- `app/Modules/Console/QueueMonitors/navigation.php`
- `app/Modules/Console/QueueMonitors/Http/Controllers/QueueMonitorController.php`
- `app/Modules/Console/QueueMonitors/Services/QueueMonitorService.php`
- `resources/js/pages/console/queue-monitor/index.tsx`
- `resources/js/pages/console/queue-monitor/types.ts`
- `resources/js/pages/console/queue-monitor/queue-monitor-components/queue-workspace-card.tsx`
- `resources/js/pages/console/queue-monitor/queue-monitor-components/queue-overview-cards.tsx`
- `resources/js/pages/console/queue-monitor/queue-monitor-components/queue-pager.tsx`
- `tests/Feature/QueueMonitorTest.php`

## Contract modul

`Console.QueueMonitors` adalah runtime operation module untuk melihat pending/failed jobs dan melakukan controlled action pada failed jobs.

Boundary utama:

- route wajib `auth`;
- view wajib `queue-monitor.view`;
- manage failed job wajib `queue-monitor.manage`;
- pending/failed job list tidak mengekspos raw payload;
- failed job exception hanya ditampilkan sebagai summary terbatas dan sudah di-redact untuk pola secret;
- manage action hanya memakai command Laravel queue resmi:
  - `queue:retry`;
  - `queue:forget`;
  - `queue:flush`;
- module ini tidak boleh menjadi bypass business authorization. Retry job hanya mengembalikan job ke queue; job tetap harus menegakkan authorization/business invariant-nya sendiri saat dieksekusi.

## Module export

| Export | Status | Catatan |
|---|---:|---|
| `routes` | yes | index, retry, forget, flush |
| `permissions` | yes | `queue-monitor.view`, `queue-monitor.manage` |
| `navigation` | yes | menu Operasional → Queue Monitor |
| `events/listeners` | no | runtime control langsung |
| `integrations` | no | bukan contract eksternal |

## Route dan permission

| Route | Method | Aksi | Guard |
|---|---:|---|---|
| `/queue-monitor` | `GET` | overview, pending jobs, failed jobs | `auth` + `queue-monitor.view` |
| `/queue-monitor/failed/{uuid}/retry` | `POST` | retry failed job | `auth` + `queue-monitor.manage` |
| `/queue-monitor/failed/{uuid}` | `DELETE` | forget/delete failed job | `auth` + `queue-monitor.manage` |
| `/queue-monitor/failed` | `DELETE` | flush all failed jobs | `auth` + `queue-monitor.manage` |

Default role mapping:

- `admin`: `queue-monitor.view`, `queue-monitor.manage`;
- `staff`: tidak mendapat permission default.

## Read behavior

`QueueMonitorService::overview()` membaca:

- queue connection aktif;
- jobs table;
- failed jobs table;
- jumlah pending;
- jumlah reserved;
- jumlah failed.

`pendingJobs()` membaca table jobs database dan mengirim field aman:

- id;
- queue;
- job name dari payload display name/command name;
- attempts;
- reserved_at;
- available_at;
- created_at.

`failedJobs()` membaca table failed jobs dan mengirim field terbatas:

- id;
- uuid;
- connection;
- queue;
- job name;
- exception summary maksimal 260 karakter;
- failed_at.

Raw payload job dan raw exception full stack trace tidak dikirim ke frontend.

## Manage behavior

Manage action:

- `retry($uuid)` → `Artisan::call('queue:retry', ['id' => [$uuid]])`;
- `forget($uuid)` → `Artisan::call('queue:forget', ['id' => $uuid])`;
- `flushFailed()` → `Artisan::call('queue:flush')`.

Controller melakukan authorization sebelum memanggil service. Frontend juga disable tombol manage jika `can.manage = false`, tetapi ini hanya UX guard; security tetap ada di controller.

## Hardening yang diterapkan

Sebelum task ini, exception summary mengambil baris pertama exception dan membatasi panjangnya. Pada task ini ditambahkan redaction untuk pola:

- `password=...`;
- `token=...`;
- `secret=...`;
- `api_key=...`;
- `api-key=...`;
- `apikey=...`;

Nilai setelah `=` atau `:` diganti menjadi `[redacted]`.

Contoh:

```text
RuntimeException: token=[redacted] api_key=[redacted] password=[redacted]
```

Ini tidak menggantikan kewajiban job untuk tidak memasukkan secret ke exception, tetapi mengurangi risiko kebocoran di UI Queue Monitor.

## Frontend behavior

Halaman Queue Monitor menampilkan:

- header connection;
- overview cards;
- queue filter;
- refresh;
- pending jobs table;
- failed jobs table;
- retry/delete per failed job;
- clear all failed jobs.

Manage action:

- memakai `window.confirm()` sebelum retry/delete/flush;
- tombol disabled jika user tidak punya manage permission;
- tetap diverifikasi backend.

## Security review

Yang sudah baik:

- view dan manage permission terpisah;
- user tanpa view ditolak;
- user tanpa manage ditolak untuk retry/forget/flush;
- raw payload tidak dikirim ke UI;
- exception hanya summary dan sudah di-redact untuk pola secret umum;
- pending/failed list dipaginate;
- missing jobs/failed table menghasilkan paginator kosong, bukan error fatal.

Temuan/residual risk:

1. Retry job dapat mengeksekusi ulang side effect.
   - Ini by design untuk operator.
   - Perlu operator memahami konsekuensi retry.
2. Flush failed jobs irreversible.
   - UI sudah confirm, backend permission-gated.
   - Belum ada audit log untuk action retry/forget/flush pada task ini.
3. Redaction exception berbasis pola sederhana.
   - Jika exception menulis secret tanpa nama key, redaction tidak bisa mendeteksi.
4. Queue Monitor membaca table sesuai config queue aktif.
   - Untuk driver selain database, read behavior bisa terbatas/empty.
5. Runtime operation tidak boleh dipakai untuk bypass domain authorization.
   - Job harus tetap idempotent dan enforce invariant saat dijalankan.

## Guide-plan koreksi lanjutan

### QM-01 — Audit runtime queue actions

**Tujuan:** mencatat operator yang melakukan retry/forget/flush.

**Rencana:**

1. Inject `AuditLogService` ke `QueueMonitorService` atau controller.
2. Audit event:
   - `QueueMonitor.failed_retry_requested`;
   - `QueueMonitor.failed_forgotten`;
   - `QueueMonitor.failed_flushed`.
3. Payload audit hanya uuid/count, bukan raw payload job.

### QM-02 — Operator safety copy

**Tujuan:** mengurangi risiko salah klik pada retry/flush.

**Rencana:**

1. Ganti `window.confirm()` menjadi dialog yang menjelaskan dampak.
2. Untuk flush, tampilkan jumlah failed jobs yang akan dihapus.
3. Pertimbangkan typed confirmation untuk flush jika production.

### QM-03 — Queue driver compatibility note

**Tujuan:** menjelaskan behavior bila queue driver bukan database.

**Rencana:**

1. Tambahkan health warning jika jobs/failed jobs table tidak tersedia.
2. Dokumentasikan bahwa MVP monitor dioptimalkan untuk database queue.

## Acceptance review

- [x] View dan manage permission terpisah.
- [x] Retry/forget/flush denial matrix hijau.
- [x] Payload failed job tidak mengekspos secret berlebih.
- [x] Runtime operation boundary terdokumentasi.
- [x] Residual risk retry/flush dicatat.

## Evidence

```bash
php artisan test --filter=QueueMonitor
vendor/bin/pint --test app/Modules/Console/QueueMonitors tests/Feature/QueueMonitorTest.php resources/js/pages/console/queue-monitor
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

Hasil terakhir:

- `QueueMonitorTest`: 5 tests, 23 assertions, pass.
- Pint targeted: pass.
- TypeScript typecheck: pass.
- Vite build: pass.
- Module contract validation: pass.
- Diff whitespace check: pass.
