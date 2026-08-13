---
id: EVT-CATALOG-001
title: Katalog Event Integrasi
document_type: event-catalog
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0002, DEP-HR-001]
---

# Katalog Event Integrasi

| ID | Event | Producer | Consumer production yang ditemukan | Delivery | Versi schema | Status |
|---|---|---|---|---|---:|---|
| `EVT-HR-MOV-001` | `EmployeeMovementAppliedV1` | `HR/EmployeeMovements` | Tidak ditemukan | Laravel event/in-process | 1 | producer aktif, tanpa consumer |
| `EVT-HR-OFF-001` | `EmployeeOffboardingCompletedV1` | `HR/Offboardings` | Tidak ditemukan | Laravel event/in-process | 1 | producer aktif, tanpa consumer |

## Deklarasi yang Belum Menjadi Event Aktif

Registry `HR/IntegrationContracts` mencantumkan sepuluh nama event HR v1. Selain dua pemetaan di atas, pencarian kode tidak menemukan publisher production untuk nama-nama registry tersebut. Karena itu, nama berikut berstatus `deferred`, bukan kontrak aktif:

- `EmployeeCreatedV1`
- `EmployeeProfileUpdatedV1`
- `EmployeeContractChangedV1`
- `EmployeeDocumentComplianceChangedV1`
- `EmployeeOnboardingActivatedV1`
- `EmployeeOnboardingCompletedV1`
- `EmployeeOffboardingReadyV1`
- `EmployeeOffboardingFinalizedV1`
- `EmploymentTerminatedV1`

`EmployeeAssignmentChangedV1` dipetakan oleh registry ke `EmployeeMovementAppliedV1`; nama alias registry tidak dinyatakan sebagai event kedua yang benar-benar dipublikasikan.

## Aturan

1. Event dimiliki modul producer dan ditempatkan pada `Integration/Events/` bila menjadi kontrak lintas modul.
2. Entri baru harus mempunyai makna, pemicu, producer, schema, delivery semantics, sensitive fields, dan idempotency rule.
3. Event pada manifest atau registry tanpa publisher tidak diberi status aktif.
4. Tidak ada jaminan queue, retry, outbox, atau delivery lintas server pada baseline saat ini.
