# ADR-004 — Publish minimal offboarding completed event v1

**Status:** Accepted
**Tanggal:** 2026-07-17

## Konteks

Offboardings sudah memiliki finalisasi effective-dated yang mengubah Offboarding, Employee, dan optional Employee Contract secara atomic melalui gateway resmi pemilik domain. Setelah itu, domain lain seperti Attendance, Payroll, Accounting, Document Management, atau Console access control kemungkinan membutuhkan sinyal perubahan.

Gate awal menunda event sampai consumer approval tersedia. Setelah gate disetujui, delivery, retry, dan ordering semantics untuk MVP disepakati sebagai event signal minimal tanpa listener downstream.

## Keputusan

- Offboardings mempublikasikan `EmployeeOffboardingCompletedV1`.
- Manifest Offboardings mendaftarkan event pada `events` dan tetap mempertahankan `listeners: []`.
- Event dikirim setelah transaksi finalization berhasil.
- Payload hanya membawa identifier, status/type, effective date, business date, dan actor id minimum.
- Schema JSON versioned menjadi public contract.
- Tidak ada dependency langsung ke Attendance, Payroll, Accounting, Document Management, atau consumer downstream lain.
- Tidak dibuat listener, outbox, adapter, projector, atau mutation downstream pada MVP.

## Delivery/retry/ordering semantics

- **Delivery:** synchronous Laravel domain event setelah finalization transaction sukses.
- **Retry:** producer tidak melakukan retry otomatis pada MVP. Consumer wajib idempotent berdasarkan `event_id`; finalize retry yang sudah completed tidak mengirim event kedua.
- **Ordering:** ordering hanya per Offboarding aggregate berdasarkan `finalized_at`/`occurred_at`; tidak ada global ordering lintas employee.
- **Failure:** event bukan sumber konsistensi HR internal. Employee, Contract, dan Offboarding sudah commit atomic sebelum event dikirim.

## Payload contract

Field yang dipublikasikan:

- `schema_version`;
- `offboarding_id`;
- `employee_id`;
- `employee_contract_id`;
- `target_employment_status_id`;
- `exit_type`;
- `effective_date`;
- `business_date`;
- `finalized_by_user_id`.

Tidak dipublikasikan:

- nama employee;
- employee number;
- exit reason;
- notes;
- file/document reference;
- storage path;
- active identity atau request fingerprint.

## Konsekuensi

- Downstream dapat mulai membuat consumer contract test terhadap event v1.
- Perubahan field breaking membutuhkan ADR pengganti dan schema version baru.
- Kebutuhan retry durable/outbox dapat dievaluasi pada fase integration hardening berikutnya.
