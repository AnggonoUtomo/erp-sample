# ADR-001: HR Reports memakai read-only reporting boundary

## Status

Accepted

## Date

2026-07-18

## Context

Project HR sudah memiliki module sumber untuk data operasional:

- `Employees` sebagai profile utama employee.
- `EmployeeContracts` sebagai lifecycle kontrak.
- `EmployeeDocuments` sebagai metadata dokumen HR dan expiry.
- `EmployeeMovements` sebagai histori perubahan profile.
- `Onboardings` dan `Offboardings` sebagai workflow masuk/keluar employee.

HR membutuhkan report lintas data tersebut, tetapi report berisiko menjadi jalur mutation tidak resmi jika diberi kemampuan memperbaiki data, mengirim reminder, melakukan archive/restore, atau membuka file langsung. Risiko lainnya adalah kebocoran data sensitif, terutama nomor dokumen, notes internal, reason confidential, storage path, URL, token, dan data kompensasi.

## Decision

`HRReports` diputuskan sebagai module read-only.

MVP hanya boleh:

- membaca data dari module HR sumber;
- menerima filter/tanggal eksplisit;
- mengembalikan projection minimal untuk report;
- menyediakan command read-only untuk summary/expiry;
- menyediakan page report tanpa action mutation.

MVP tidak boleh:

- membuat, mengubah, menghapus, approve, cancel, apply, archive, restore, atau finalize data module sumber;
- mengirim notification;
- membuka/download file DMS;
- membuat export Excel/PDF sebelum gate export disetujui;
- membuat snapshot/reporting table baru sebelum ada kebutuhan volume/performance yang jelas.

Report awal:

1. Headcount by Departement.
2. Headcount by Work Location.
3. Employment Status Summary.
4. Contract Expiry.
5. Document Expiry.

## Alternatives considered

### Membuat report sekaligus dengan action operasional

Contoh action: approve contract dari report, verify document dari report, atau kirim reminder expiry.

- Pros: user dapat bertindak cepat dari satu halaman.
- Cons: report menjadi mutation path kedua dan authorization/audit semakin sulit dijaga.
- Rejected: lifecycle mutation harus tetap berada di module pemilik data.

### Membuat reporting snapshot/table sejak awal

- Pros: lebih siap untuk data besar dan query agregasi kompleks.
- Cons: menambah sync, stale data, projector, dan recovery complexity terlalu awal.
- Rejected for MVP: query langsung cukup sampai volume data membuktikan kebutuhan snapshot.

### Menggabungkan HR Reports dengan dashboard HR lama

- Pros: lebih sedikit menu.
- Cons: dashboard cenderung visual/ringkasan, sedangkan HR Reports butuh filter, tabel, command, dan contract query yang lebih formal.
- Rejected: HR Reports dibuat sebagai boundary sendiri agar mudah diperluas dan diuji.

### Langsung membuat export Excel/PDF

- Pros: sering dibutuhkan pengguna bisnis.
- Cons: export memperbesar risiko data leak, performance issue, dan format menjadi contract publik terlalu cepat.
- Rejected for MVP: export menunggu read model stabil.

## Consequences

- Implementasi awal lebih aman dan kecil.
- HR user dapat membaca data penting tanpa risiko mengubah lifecycle.
- Semua mutation tetap berada di module sumber dengan policy/audit masing-masing.
- Export dan advanced analytics membutuhkan ADR/task lanjutan.
- Jika data membesar, keputusan baru diperlukan untuk snapshot/reporting table atau queued export.

## Verification

Sebelum HR Reports dianggap selesai:

- route inventory membuktikan tidak ada non-GET mutation route di MVP;
- tests membuktikan command dan service tidak menulis database/audit/notification/queue/file;
- sensitive-data test memastikan expiry report tidak memuat nomor dokumen plaintext, DMS reference, storage path, URL, token, notes internal, atau data kompensasi;
- documentation menjelaskan export/snapshot sebagai deferred.
