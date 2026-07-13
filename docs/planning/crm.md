# Spec: CRM

## Objective

Mengelola account/contact, lead, opportunity, aktivitas, dan handoff penjualan dengan ownership serta audit yang jelas.

## Submodule dan urutan

1. `Accounts` dan `Contacts`.
2. `Leads` dan `LeadAssignments`.
3. `Pipelines` dan `Opportunities`.
4. `Activities`, `Tasks`, dan `Notes`.
5. `Products`, `PriceBooks`, `Quotes`.
6. `CRMReports` dan `CRMIntegrations`.

## Requirements

- Deduplication account/contact terukur dan merge selalu auditable/reversible.
- Ownership dan visibility enforced server-side.
- Pipeline stage tervalidasi; won/lost menyimpan reason dan timestamp.
- Attachment memakai [Document Management](document-management.md), sementara metadata bisnis tetap di CRM.

## Non-scope

Marketing automation, helpdesk, invoicing/ledger, email provider tertentu, dan customer portal pada fase awal.

## Command design

`crm:detect-duplicates`, `crm:reassign-owner`, `crm:stale-opportunities`, dan `crm:import` menyediakan `--dry-run`, report terstruktur, serta idempotency key untuk import.

## Acceptance criteria dan test plan

- Tenant/ownership isolation, duplicate detection, merge rollback, valid stage transition, quote totals, mutation denial matrix, import retry, audit log, frontend typecheck/build teruji.

## Risks

PII leakage, duplicate customer, unrestricted bulk reassignment, arbitrary stage transitions, dan attachment authorization mismatch.
