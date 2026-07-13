# ADR-001: Model Employee Contract sebagai Effective-dated Record

## Status

Accepted

## Date

2026-07-13

## Context

Employee dapat berganti kontrak dan tipe hubungan kerja sepanjang masa kerja. Attendance dan Payroll perlu mengetahui kontrak yang berlaku pada tanggal/periode tertentu, bukan sekadar nilai terbaru pada profile Employee. Mengubah satu row kontrak aktif akan menghilangkan fakta historis dan dapat membuat payroll lama tidak dapat direproduksi.

Masalah yang harus diselesaikan:

- rentang tanggal harus deterministik pada boundary;
- dua kontrak tidak boleh berlaku bersamaan untuk employee yang sama;
- kontrak tanpa end date harus dapat diakhiri atau diganti dengan aman;
- koreksi dan perubahan harus meninggalkan audit trail;
- consumer lintas project tidak boleh bergantung pada schema tabel internal HR.

## Decision

Setiap kontrak disimpan sebagai record effective-dated dengan `start_date` inklusif dan `end_date` inklusif atau nullable. Sebuah kontrak berlaku pada tanggal `D` ketika:

```txt
start_date <= D AND (end_date IS NULL OR end_date >= D)
```

Untuk employee yang sama, periode contract non-cancelled dan non-archived tidak boleh overlap. Kontrak yang sudah aktif tidak diubah melalui generic update. Perubahan dilakukan melalui transition eksplisit:

- `terminate`: menetapkan akhir kontrak dan reason;
- `cancel`: mengeluarkan kontrak dari effective history;
- `supersede`: secara atomic mengakhiri/menautkan kontrak lama dan membuat kontrak pengganti.

Contract lama menyimpan `superseded_by_id` agar rantai perubahan dapat ditelusuri. Snapshot lintas project membawa schema version dan interval efektif, bukan model Eloquent atau seluruh row database.

## Alternatives considered

### Satu current contract pada tabel Employees

- Kelebihan: query sederhana dan sedikit tabel.
- Kekurangan: histori hilang, backdated lookup mustahil, dan Payroll lama tidak reproducible.
- Ditolak karena tidak memenuhi kebutuhan audit dan effective-date.

### Contract history berupa JSON

- Kelebihan: schema awal fleksibel.
- Kekurangan: constraint overlap, FK, filtering expiry, dan reporting sulit dijamin database.
- Ditolak karena contract adalah relational domain dengan invariant kuat.

### Event-only ledger tanpa current projection

- Kelebihan: histori lengkap dan append-only.
- Kekurangan: kompleksitas event sourcing terlalu tinggi untuk kebutuhan sekarang, replay/versioning menambah risiko operasional.
- Ditolak untuk rilis awal; audit log + effective-dated rows sudah memadai.

### Mengizinkan overlap dan memilih record terbaru

- Kelebihan: input lebih longgar.
- Kekurangan: hasil tergantung sorting/created time dan dapat menghitung employee dua kali.
- Ditolak karena contract yang berlaku harus tunggal dan deterministik.

## Consequences

### Positive

- Riwayat kontrak dapat ditelusuri dan query pada tanggal historis deterministik.
- Payroll/Attendance snapshot dapat direproduksi.
- Lifecycle business terlihat jelas melalui transition dan audit.
- Contract replacement dapat dilakukan atomic tanpa overwrite histori.

### Negative

- Overlap validation memerlukan query interval, transaction, dan concurrency control.
- Koreksi data aktif lebih ketat daripada CRUD biasa.
- Database yang berbeda mungkin membutuhkan strategi lock/constraint berbeda.
- Open-ended contract harus diakhiri atau disupersede sebelum periode baru dibuat.

## Implementation constraints

- Overlap check dilakukan kembali di dalam transaction saat activation/supersede.
- Lock employee atau contract set untuk mencegah race; strategi final mengikuti database production.
- Restore soft-deleted contract menjalankan seluruh invariant kembali.
- Force delete tidak disediakan pada rilis awal.
- Dates memakai business date, sedangkan audit/event timestamp tetap UTC.
- Perubahan semantics interval atau payload snapshot membutuhkan ADR baru dan schema version baru.

## Validation

ADR disetujui pada 2026-07-13. Sebelum implementasi production dinyatakan selesai:

- stakeholder menyetujui inclusive end date dan larangan overlap;
- database production serta strategi locking dikonfirmasi;
- open questions pada [specification](../specification.md#10-open-questions) diputuskan;
- Task 01 diizinkan masuk tahap implementasi.
