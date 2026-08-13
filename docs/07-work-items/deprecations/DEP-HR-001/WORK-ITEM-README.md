# DEP-HR-001 — Deprecation Modul Teknis HR IntegrationContracts

```yaml
id: DEP-HR-001
kind: deprecation-removal
classification: CRITICAL
status: approved
owner: unassigned
created_at: 2026-08-13
updated_at: 2026-08-13
parent: ARC-DDD-LITE-001
discovered_by: TSK-ARC-DDD-LITE-001-00
depends_on: [ADR-0002]
blocks: []
related_adrs: [ADR-0002]
affected_boundaries: [HR]
affected_modules: [IntegrationContracts, Employees, EmployeeContracts, EmployeeDocuments]
```

## Tujuan

Menghentikan `HR/IntegrationContracts` sebagai shell modul teknis, memindahkan contract/DTO/event yang masih diperlukan ke modul bisnis pemilik, dan mempertahankan perilaku serta aturan privacy/versioning.

## Alasan Klasifikasi

Penghapusan modul arsitektural, perubahan namespace contract, dan kontrol minimisasi PII memerlukan Human Decision Gate. Tidak ada removal yang diizinkan hanya berdasarkan hasil search consumer.

## Status

Proposal dan consumer inventory telah disetujui. Work item belum `ready`; approval ini tidak mengizinkan penghapusan kode sebelum checklist readiness dan removal gate terpenuhi.
