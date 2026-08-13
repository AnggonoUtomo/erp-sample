---
id: INT-CATALOG-001
title: Katalog Integrasi Internal
document_type: integration-catalog
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0002, DEP-HR-001]
---

# Katalog Integrasi Internal

Katalog ini hanya mencatat surface yang ditemukan pada kode. Keberadaan class bukan bukti adanya consumer production; kolom status membedakannya.

| ID | Provider/Pemilik | Contract atau mekanisme | Consumer production yang ditemukan | Klasifikasi data | Status |
|---|---|---|---|---|---|
| `INT-HR-CON-001` | `HR/EmployeeContracts` | `EmployeeContractSnapshotReader` v1 | Tidak ditemukan di luar binding; test tersedia | internal HR | existing |
| `INT-HR-CON-002` | `HR/EmployeeContracts` | `EmployeeContractTerminationGateway` v1 | `HR/Offboardings` | internal HR | active |
| `INT-HR-CON-003` | `HR/EmployeeContracts` | `EmployeeContractEmploymentTypeGuard` | `HR/EmployeeMovements` | internal HR | active |
| `INT-HR-EMP-001` | `HR/Employees` | `EmployeeTerminationGateway` v1 | `HR/Offboardings` | internal HR | active |
| `INT-HR-DOC-001` | `HR/EmployeeDocuments` | `EmployeeDocumentAttachmentGateway` v1 | service dalam `HR/EmployeeDocuments`; adapter menuju DMS | sensitif/dokumen | active |
| `INT-HR-DOC-002` | `HR/EmployeeDocuments` | `DocumentReferenceReader` v1 | test/fake; contract tercantum pada manifest | sensitif/dokumen | existing |
| `INT-DMS-001` | `DocumentManagement/Foundation` | `DocumentReferenceReader` v1 | adapter HR EmployeeDocuments | sensitif/dokumen | active |
| `INT-DMS-002` | `DocumentManagement/Foundation` | `DocumentAccessGateway` v1 | alur secure delivery | sensitif/dokumen | active |
| `INT-DMS-003` | `DocumentManagement/Foundation` | `DocumentDeliveryGateway` v1 | alur secure delivery | sensitif/dokumen | active |
| `INT-DMS-004` | `DocumentManagement/Foundation` | `DocumentIngestionGateway` v1 | adapter HR EmployeeDocuments | sensitif/dokumen | active |
| `INT-HR-LEG-001` | `HR/IntegrationContracts` | empat snapshot provider v1 | Hanya test yang ditemukan | internal HR/PII terbatas | deprecation-candidate |

## Aturan

- Contract aktif tetap versioned dan additive bila kompatibilitas perlu dijaga.
- Payload menggunakan data minimum yang dibutuhkan consumer.
- Consumer tidak boleh mengganti contract dengan direct model mutation.
- Pemetaan detail deprecation `INT-HR-LEG-001` berada pada `DEP-HR-001`.
- Public API, webhook, outbox, atau broker tidak dianggap ada tanpa implementasi dan work item tersendiri.
