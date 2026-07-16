# Task 16 — Integration event v1 gate

**Hasil:** APPROVED + IMPLEMENTED
**Tanggal evaluasi awal:** 2026-07-17
**Tanggal approval semantics:** 2026-07-17

## Evidence yang diperiksa

- Gate awal menahan event sampai consumer dan semantics disetujui.
- Approval berikutnya menyatakan integration event gate approved serta delivery, retry, dan ordering semantics disetujui.
- Effective exit sudah selesai secara atomic di domain HR melalui gateway resmi Employees dan Employee Contracts, sehingga event downstream bukan prasyarat konsistensi internal HR.
- Event v1 kini hanya memberi sinyal bahwa finalization telah sukses; tidak melakukan mutation downstream.

## Contract yang dipublikasikan

Event: `EmployeeOffboardingCompletedV1`

Schema: `app/Modules/HR/Offboardings/Integration/Schemas/employee-offboarding-completed-v1.json`

Payload minimal:

- `schema_version`;
- `offboarding_id`;
- `employee_id`;
- `employee_contract_id`;
- `target_employment_status_id`;
- `exit_type`;
- `effective_date`;
- `business_date`;
- `finalized_by_user_id`.

Payload tidak membawa nama employee, employee number, exit reason, notes, file evidence, storage reference, atau PII bebas.

## Delivery/retry/ordering semantics

- **Delivery:** synchronous Laravel domain event setelah transaksi finalization berhasil commit.
- **Retry:** producer tidak melakukan retry otomatis pada MVP; consumer wajib idempotent berdasarkan `event_id`.
- **Ordering:** urutan dijamin hanya per aggregate Offboarding berdasarkan `finalized_at`/`occurred_at`; tidak ada global ordering antar employee.
- **Side effect:** tidak ada listener downstream terpasang pada MVP, sehingga event bersifat public signal contract saja.

Lihat [ADR-004](decisions/004-defer-integration-event-v1.md) untuk keputusan lengkap.

## Verification

```bash
php artisan test --filter=OffboardingIntegrationContract
```

Test menjaga agar manifest mempublikasikan event v1, schema tetap minimal, event terkirim satu kali setelah finalization sukses, retry finalize tidak menggandakan event, dan payload tidak membawa PII/secret.
