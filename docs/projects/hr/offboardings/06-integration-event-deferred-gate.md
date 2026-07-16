# Task 16 — Integration event v1 deferred gate

**Hasil:** DEFERRED  
**Tanggal evaluasi:** 2026-07-17

## Evidence yang diperiksa

- `docs/planning/attendance.md`, `docs/planning/payroll.md`, `docs/planning/accounting.md`, dan roadmap terkait masih menjadi rencana pengembangan, bukan consumer runtime Offboardings.
- Belum ada module runtime Attendance, Payroll, Accounting, atau DMS consumer yang mendaftarkan handler untuk event Offboardings.
- Belum ada owner consumer yang menyetujui use case, payload minimal, delivery semantics, retry, ordering, idempotency key, atau failure handling.
- Manifest Offboardings saat ini tidak mempublikasikan event/listener public dan tidak menambah dependency ke consumer downstream.
- Effective exit sudah selesai secara atomic di domain HR melalui gateway resmi Employees dan Employee Contracts, sehingga event downstream bukan prasyarat konsistensi internal HR.

## Keputusan gate

Task 16 tidak mengimplementasikan integration event v1. Ini adalah hasil yang memenuhi acceptance criteria ketika consumer belum disetujui.

Tidak dibuat:

- event class public seperti `EmployeeOffboardingCompleted`;
- schema JSON untuk event;
- adapter/listener/outbox/dispatcher downstream;
- dependency Attendance, Payroll, Accounting, Document Management, atau consumer lain;
- payload spekulatif yang membawa data employee, contract, reason, atau exit evidence.

Lihat [ADR-004](decisions/004-defer-integration-event-v1.md) untuk syarat membuka kembali gate.

## Verification

```bash
php artisan test --filter=OffboardingIntegrationContract
```

Test menjaga agar manifest event/listener tetap kosong, dependency downstream tidak muncul, dan namespace event/schema spekulatif tidak dibuat.

