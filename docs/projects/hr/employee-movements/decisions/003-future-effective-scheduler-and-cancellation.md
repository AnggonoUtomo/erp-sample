# ADR-003: Future-effective Scheduler and Cancellation

## Status

Accepted — 2026-07-17.

## Context

Employee movement sering diketahui sebelum tanggal efektifnya. Jika HR harus menunggu sampai hari H untuk membuat draft, approval operasional dan review histori menjadi sulit. Namun, future draft tidak boleh mengubah profile Employees sebelum tanggal efektif.

## Decision

Employee Movements menerima `effective_date` hari ini atau masa depan. Backdate ditolak pada create.

Movement tetap tidak memengaruhi `Employees` selama status `DRAFT`. Apply hanya boleh dilakukan saat movement sudah due. Untuk operasi terjadwal, module menyediakan command:

```bash
php artisan hr:employee-movements:apply-due --date=YYYY-MM-DD
php artisan hr:employee-movements:apply-due --date=YYYY-MM-DD --dry-run
```

Command memilih DRAFT dengan `effective_date <= --date`, memproses secara deterministik berdasarkan `effective_date` lalu `id`, dan tetap menjalankan guard apply yang sama: status DRAFT, snapshot tidak stale, target aktif, dan contract coordination.

Cancellation v1 hanya berlaku untuk DRAFT. Cancel menyimpan actor, timestamp, dan alasan. Cancelled movement tidak mengubah employee profile dan tidak dapat di-apply.

## Alternatives Considered

### Queue job per movement

Ditunda. Queue job membuat operasi tergantung worker runtime dan retry policy yang belum dibutuhkan untuk MVP.

### Auto-apply saat halaman dibuka

Ditolak karena side effect dari read/UI sulit diaudit dan dapat mengejutkan user.

### Backdate apply

Ditolak untuk slice ini karena membutuhkan reconciliation downstream untuk Attendance, Payroll, dan audit correction.

## Consequences

- HR dapat menyiapkan movement lebih awal tanpa mengubah current profile.
- Scheduler dapat dijalankan dari task scheduler Laravel/OS dengan tanggal eksplisit.
- Jika scheduler terlambat, command mengejar DRAFT yang sudah due.
- Backdate dan approval bertingkat tetap menjadi task terpisah.
