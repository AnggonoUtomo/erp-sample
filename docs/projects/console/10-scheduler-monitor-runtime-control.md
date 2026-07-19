# 10 — Scheduler Monitor Runtime Control

Dokumen ini mencatat hasil telusur `Console.SchedulerMonitors` seolah-olah modul ini baru akan dibuat. Fokusnya adalah memastikan Scheduler Monitor menjadi alat operasi runtime yang permission-gated, read-only untuk task list, dan controlled untuk menjalankan due task tanpa menjadi jalan pintas melewati authorization domain.

## Status

`Reviewed and hardened — 2026-07-19`.

Task dinyatakan selesai. Ada hardening kecil pada `SchedulerMonitorService` agar output `schedule:run` yang ditampilkan kembali ke user me-redact pola secret umum.

## Urutan baca relevan

1. [09 — Queue Monitor Runtime Control](09-queue-monitor-runtime-control.md) — boundary runtime operation untuk queue.
2. Dokumen ini — boundary Scheduler Monitor.
3. Nanti: [Checkpoint D — Runtime operation boundary](tasks.md#checkpoint-d--runtime-operation-boundary).
4. Nanti: [Task 11 — Backup Restore signed recovery boundary](tasks.md#task-11--backup-restore-signed-recovery-boundary).

## Source yang ditelusuri

- `app/Modules/Console/SchedulerMonitors/module.php`
- `app/Modules/Console/SchedulerMonitors/routes.php`
- `app/Modules/Console/SchedulerMonitors/permissions.php`
- `app/Modules/Console/SchedulerMonitors/navigation.php`
- `app/Modules/Console/SchedulerMonitors/Http/Controllers/SchedulerMonitorController.php`
- `app/Modules/Console/SchedulerMonitors/Services/SchedulerMonitorService.php`
- `resources/js/pages/console/scheduler-monitor/index.tsx`
- `resources/js/pages/console/scheduler-monitor/types.ts`
- `resources/js/pages/console/scheduler-monitor/scheduler-monitor-components/scheduled-task-card.tsx`
- `resources/js/pages/console/scheduler-monitor/scheduler-monitor-components/scheduler-overview-cards.tsx`
- `resources/js/pages/console/scheduler-monitor/scheduler-monitor-components/scheduler-side-panels.tsx`
- `resources/js/pages/console/scheduler-monitor/scheduler-monitor-components/scheduler-monitor-header.tsx`
- `routes/console.php`
- `tests/Feature/SchedulerMonitorTest.php`

## Contract modul

`Console.SchedulerMonitors` adalah runtime operation module untuk melihat daftar scheduled task Laravel dan menjalankan due task secara manual saat dibutuhkan operator.

Boundary utama:

- route wajib `auth`;
- view wajib `scheduler-monitor.view`;
- run due task wajib `scheduler-monitor.manage`;
- scheduled task list bersifat read-only;
- run due task hanya menjalankan `schedule:run`, bukan arbitrary command;
- output run due task di-redact untuk pola secret umum sebelum masuk session flash;
- heartbeat scheduler dicatat internal lewat cache sebagai observability ringan;
- module ini tidak boleh menjadi bypass business authorization. Command yang dijalankan scheduler tetap wajib menjaga invariant dan authorization/domain safety masing-masing.

## Module export

| Export | Status | Catatan |
|---|---:|---|
| `routes` | yes | index dan run due task |
| `permissions` | yes | `scheduler-monitor.view`, `scheduler-monitor.manage` |
| `navigation` | yes | menu Operasional → Scheduler Monitor |
| `events/listeners` | no | runtime control langsung |
| `integrations` | no | bukan contract eksternal |

## Route dan permission

| Route | Method | Aksi | Guard |
|---|---:|---|---|
| `/scheduler-monitor` | `GET` | overview, heartbeat, dan scheduled task list | `auth` + `scheduler-monitor.view` |
| `/scheduler-monitor/run` | `POST` | menjalankan due scheduled tasks | `auth` + `scheduler-monitor.manage` |

Default role mapping:

- `admin`: `scheduler-monitor.view`, `scheduler-monitor.manage`;
- `staff`: tidak mendapat permission default.

## Read behavior

`SchedulerMonitorService::overview()` membaca:

- timezone dari `config('app.timezone')`;
- PHP binary path;
- artisan path;
- cron command yang harus dipasang di server;
- jumlah scheduled tasks;
- jumlah task due dalam 24 jam;
- heartbeat scheduler terakhir.

`SchedulerMonitorService::events()` menjalankan:

```bash
schedule:list --json --next --timezone=<app timezone>
```

Output JSON dinormalisasi menjadi:

- command;
- expression;
- description;
- timezone;
- next due date;
- `is_due_soon`;
- mutex/overlap info jika tersedia dari Laravel.

Jika `schedule:list` gagal atau output tidak valid, service mengembalikan list kosong agar halaman monitor tetap aman dibuka.

## Timezone dan date semantics

Timezone utama Scheduler Monitor mengikuti `config('app.timezone')`.

Aturan tanggal:

- `schedule:list` dipanggil dengan `--timezone=<app timezone>`;
- `next_due` diparse dengan `Carbon::parse()`;
- tampilan tanggal memakai format `d M Y H:i:s`;
- `due_24h` dihitung dari `now()` sampai `now()->addDay()`;
- `is_due_soon` bernilai true jika `next_due <= now()->addDay()`;
- heartbeat age dihitung dari waktu server aplikasi.

Implikasi operasional:

- jika server dan app timezone tidak sinkron, tampilan due task bisa membingungkan operator;
- konfigurasi timezone sebaiknya divalidasi saat System Settings/ops review;
- scheduler cron server tetap harus menjalankan `php artisan schedule:run` setiap menit.

## Heartbeat behavior

`routes/console.php` mendaftarkan heartbeat:

```php
Schedule::call(fn () => app(SchedulerMonitorService::class)->recordHeartbeat())
    ->name('scheduler-monitor:heartbeat')
    ->everyMinute()
    ->withoutOverlapping();
```

Heartbeat disimpan di cache key `scheduler-monitor.last_heartbeat` dengan TTL 10 menit.

Status heartbeat:

| Status | Kondisi | Makna |
|---|---|---|
| `never` | belum ada timestamp | scheduler belum pernah tercatat berjalan |
| `fresh` | age ≤ 120 detik | scheduler kemungkinan berjalan normal |
| `stale` | age ≤ 600 detik | scheduler terlambat atau cron tidak stabil |
| `down` | age > 600 detik | scheduler kemungkinan tidak berjalan |

## Manage behavior

`SchedulerMonitorService::runDueTasks()` menjalankan:

```bash
schedule:run
```

Lalu mengembalikan output Artisan. Jika tidak ada output, service mengembalikan pesan default:

```text
Scheduler dijalankan. Tidak ada output dari schedule:run.
```

Output tersebut di-redact sebelum dikirim ke frontend lewat session flash.

## Hardening yang diterapkan

Sebelum task ini, output `schedule:run` langsung dikembalikan ke user. Pada task ini ditambahkan redaction untuk pola:

- `password=...`;
- `token=...`;
- `secret=...`;
- `api_key=...`;
- `api-key=...`;
- `apikey=...`.

Nilai setelah `=` atau `:` diganti menjadi `[redacted]`.

Contoh:

```text
Running task token=[redacted] api_key=[redacted] password=[redacted]
```

Ini bukan pengganti disiplin command agar tidak menulis secret ke output, tetapi mengurangi risiko kebocoran saat operator melihat hasil `Run Due Tasks`.

## Frontend behavior

Halaman Scheduler Monitor menampilkan:

- header timezone/runtime;
- overview cards;
- scheduled task list;
- panel heartbeat/cron command;
- tombol refresh;
- tombol `Run Due Tasks` jika user punya manage permission.

Manage action:

- memakai `window.confirm()` sebelum menjalankan due task;
- tombol disabled jika user tidak punya manage permission;
- tetap diverifikasi backend.

## Security review

Yang sudah baik:

- view dan manage permission terpisah;
- user tanpa view ditolak;
- user tanpa manage ditolak untuk menjalankan scheduler;
- scheduler list read-only;
- manage action hanya `schedule:run`, bukan command arbitrary;
- output run sudah di-redact untuk pola secret umum;
- heartbeat memberi sinyal apakah cron scheduler berjalan.

Temuan/residual risk:

1. `schedule:run` dapat memicu side effect domain.
   - Ini by design untuk operator.
   - Command/job/domain service tetap wajib idempotent dan enforce invariant.
2. Belum ada audit log untuk aksi `Run Due Tasks`.
   - Permission sudah ada, tetapi trace operator lebih kuat jika diaudit.
3. Redaction output berbasis pola sederhana.
   - Jika output menulis secret tanpa nama key, redaction tidak bisa mendeteksi.
4. Heartbeat bergantung pada cache.
   - Jika cache flush/restart, status bisa kembali `never` sampai scheduler jalan lagi.
5. `window.confirm()` masih MVP.
   - Untuk production bisa diganti dialog yang lebih informatif.

## Guide-plan koreksi lanjutan

### SM-01 — Audit runtime scheduler actions

**Tujuan:** mencatat operator yang menjalankan `Run Due Tasks`.

**Rencana:**

1. Inject `AuditLogService` ke controller/service.
2. Audit event `SchedulerMonitor.run_due_tasks_requested`.
3. Payload audit cukup berisi actor, waktu, timezone, dan output summary redacted.

### SM-02 — Operator safety dialog

**Tujuan:** mengurangi risiko salah klik menjalankan task runtime.

**Rencana:**

1. Ganti `window.confirm()` menjadi dialog.
2. Jelaskan bahwa aksi bisa menjalankan command due dan side effect.
3. Tampilkan timezone aktif dan jumlah due 24 jam.

### SM-03 — Scheduler health warning

**Tujuan:** membuat status heartbeat lebih mudah dipahami user operasional.

**Rencana:**

1. Tambahkan warning UI jika heartbeat `stale`, `down`, atau `never`.
2. Tampilkan cron command copyable.
3. Dokumentasikan langkah verifikasi cron server.

## Acceptance review

- [x] Scheduled task list read-only tersedia.
- [x] Run due task hanya untuk manage permission.
- [x] Timezone/date semantics terdokumentasi.
- [x] Output runtime di-redact untuk pola secret umum.
- [x] Runtime operation boundary dan residual risk dicatat.

## Evidence

```bash
php artisan test --filter=SchedulerMonitor
vendor/bin/pint --test app/Modules/Console/SchedulerMonitors tests/Feature/SchedulerMonitorTest.php resources/js/pages/console/scheduler-monitor
npm run typecheck
npm run build
php artisan module:validate
git diff --check
```

Hasil terakhir:

- `SchedulerMonitorTest`: 6 tests, 9 assertions, pass.
- Pint targeted: pass.
- TypeScript typecheck: pass.
- Vite build: pass.
- Module contract validation: pass.
- Diff whitespace check: pass.
