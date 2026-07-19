# Checkpoint D — Runtime Operation Boundary

Tanggal checkpoint: 2026-07-19  
Status: pass untuk dokumentasi baseline Console Task 09–10

Checkpoint ini mengunci hasil telusur area runtime operation Console: Queue Monitor dan Scheduler Monitor. Tujuannya memastikan operator punya alat untuk melihat dan mengendalikan runtime process secara terbatas, tetapi action tersebut tetap permission-gated, tidak mengekspos payload/secret berlebih, dan tidak menjadi bypass business authorization.

## Scope checkpoint

Dokumen yang menjadi input:

1. [09 — Queue Monitor Runtime Control](09-queue-monitor-runtime-control.md)
2. [10 — Scheduler Monitor Runtime Control](10-scheduler-monitor-runtime-control.md)

Area source yang menjadi fokus:

- `Console.QueueMonitors`;
- `Console.SchedulerMonitors`;
- queue failed job listing;
- queue retry/forget/flush action;
- scheduler task list;
- scheduler `Run Due Tasks`;
- permission split view/manage;
- redaction output runtime;
- runtime operation residual risk.

## Kesimpulan

Checkpoint D dinyatakan pass.

Runtime operation boundary sudah memenuhi baseline:

- Queue Monitor memisahkan `queue-monitor.view` dan `queue-monitor.manage`;
- Scheduler Monitor memisahkan `scheduler-monitor.view` dan `scheduler-monitor.manage`;
- user tanpa view ditolak membuka halaman monitor;
- user tanpa manage ditolak melakukan action runtime;
- Queue Monitor tidak mengirim raw failed job payload ke frontend;
- failed job exception summary di-redact untuk pola secret umum;
- Scheduler Monitor hanya menjalankan `schedule:run`, bukan arbitrary command;
- output `schedule:run` di-redact untuk pola secret umum;
- Scheduler Monitor mendokumentasikan timezone/date semantics dan heartbeat;
- frontend disabled/confirm hanya UX guard; security tetap di controller/backend;
- runtime operation dicatat sebagai operator tool, bukan domain business service.

Tidak ada blocking correctness/security issue untuk lanjut ke Backup Restore signed recovery boundary.

## Acceptance criteria checkpoint

- [x] Task 09–10 selesai.
- [x] Queue/scheduler manage action permission-gated.
- [x] Runtime operations tidak menjadi bypass business authorization.

## Evidence

```bash
php artisan test --filter="QueueMonitor|SchedulerMonitor"
```

Hasil:

- 11 tests passed;
- 32 assertions.

```bash
vendor/bin/pint --test app/Modules/Console/QueueMonitors tests/Feature/QueueMonitorTest.php resources/js/pages/console/queue-monitor
```

Hasil:

- passed.

```bash
vendor/bin/pint --test app/Modules/Console/SchedulerMonitors tests/Feature/SchedulerMonitorTest.php resources/js/pages/console/scheduler-monitor
```

Hasil:

- passed.

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
php artisan module:validate
```

Hasil:

- all module contracts are valid.

```bash
git diff --check
```

Hasil:

- pass.

## Runtime control matrix

| Module | View permission | Manage permission | Manage action | Safety boundary |
|---|---|---|---|---|
| Queue Monitor | `queue-monitor.view` | `queue-monitor.manage` | retry failed job | command `queue:retry`; raw payload tidak dikirim |
| Queue Monitor | `queue-monitor.view` | `queue-monitor.manage` | forget failed job | command `queue:forget`; permission backend |
| Queue Monitor | `queue-monitor.view` | `queue-monitor.manage` | flush failed jobs | command `queue:flush`; confirm UI + permission backend |
| Scheduler Monitor | `scheduler-monitor.view` | `scheduler-monitor.manage` | run due tasks | command `schedule:run`; bukan arbitrary command |

## Threat model ringkas

| Threat | Status | Boundary |
|---|---|---|
| User tanpa permission melihat Queue Monitor | tertutup | controller abort 403 untuk `queue-monitor.view` |
| User tanpa manage retry/forget/flush failed job | tertutup | controller abort 403 untuk `queue-monitor.manage` |
| Failed job raw payload bocor ke UI | tertutup untuk MVP | UI menerima metadata terbatas, bukan raw payload |
| Exception failed job membawa secret | mitigated | exception summary di-redact untuk key sensitif umum |
| Retry job mengulang side effect berbahaya | residual risk by design | operator action; job harus idempotent dan enforce invariant |
| Flush failed jobs menghapus evidence operasional | residual risk by design | permission-gated; perlu audit action lanjutan |
| User tanpa permission melihat Scheduler Monitor | tertutup | controller abort 403 untuk `scheduler-monitor.view` |
| User tanpa manage menjalankan scheduler | tertutup | controller abort 403 untuk `scheduler-monitor.manage` |
| Scheduler menjalankan arbitrary command | tertutup | hanya `schedule:run` |
| Output scheduler membawa secret | mitigated | output di-redact untuk key sensitif umum |
| Timezone salah menyebabkan salah baca due task | residual risk | timezone semantics terdokumentasi; ops perlu validasi config |
| Runtime action bypass business authorization | residual risk lintas modul | domain command/job wajib menjaga authorization/invariant sendiri |

## Review kualitas

### Correctness

Queue dan Scheduler Monitor memenuhi behavior yang didokumentasikan. Test feature membuktikan view/manage denial matrix, retry queue, scheduler run, dan redaction output.

### Maintainability

Pola module tetap konsisten dengan Console module lain:

- `module.php`;
- `routes.php`;
- `permissions.php`;
- `navigation.php`;
- controller tipis;
- service untuk runtime read/action;
- frontend page/component terpisah.

Tidak ada dependency baru.

### Security

Boundary utama sudah ditutup:

- route wajib `auth`;
- authorization backend eksplisit;
- permission view/manage terpisah;
- secret-like output di-redact;
- raw queue payload tidak dikirim ke UI.

Residual risk runtime tetap ada karena memang operation module dapat memicu side effect. Karena itu invariant domain tidak boleh bergantung pada UI Console saja.

### Performance

Queue listing memakai pagination. Scheduler list membaca output `schedule:list`; jumlah scheduled tasks biasanya kecil dan sesuai kebutuhan operator. Tidak ditemukan unbounded frontend mutation.

### Consistency

Runtime operation modules tetap berada di Console, bukan di HR atau project bisnis lain. Ini menjaga Console sebagai operational foundation.

## Residual risk dan guide-plan

Tidak ada blocker sebelum lanjut ke Task 11. Follow-up yang tetap dicatat:

1. Audit runtime operation actions.
   - Kondisi sekarang: retry/forget/flush queue dan run scheduler belum dicatat sebagai audit event khusus.
   - Guide-plan: tambahkan event audit minimal berisi actor, action, target uuid/count, timezone, dan output summary redacted.

2. Operator safety dialog.
   - Kondisi sekarang: aksi runtime memakai `window.confirm()`.
   - Guide-plan: ganti dengan dialog yang menjelaskan dampak action; untuk flush gunakan copy yang lebih tegas.

3. Queue driver compatibility.
   - Kondisi sekarang: Queue Monitor paling cocok untuk database queue.
   - Guide-plan: beri warning/empty-state khusus jika driver/table tidak tersedia.

4. Scheduler heartbeat ops guidance.
   - Kondisi sekarang: heartbeat ada, tetapi belum ada troubleshooting runbook kecil di UI.
   - Guide-plan: tampilkan warning untuk `never/stale/down` dan copyable cron command.

5. Domain idempotency contract.
   - Kondisi sekarang: Queue/Scheduler dapat menjalankan ulang job/command.
   - Guide-plan: setiap job/command domain penting wajib punya test idempotency atau invariant guard di modul pemiliknya.

## Keputusan checkpoint

Lanjut ke Task 11 — Backup Restore signed recovery boundary.

Alasannya: runtime operation module sudah punya permission split, test denial matrix, output safety dasar, dan dokumentasi residual risk. Sisa temuan adalah hardening operasional lanjutan, bukan blocker untuk menelusuri recovery boundary.
