# Roadmap Project CRM

Roadmap ini adalah rencana awal untuk membangun project `CRM` di atas starterkit modular. Fokusnya adalah mengelola relasi pelanggan dari lead, pipeline, aktivitas sales, hingga customer lifecycle yang bisa dihubungkan dengan project lain seperti accounting, support, marketing, dan operations.

## Prinsip Utama

- Semua interaksi pelanggan harus tercatat sebagai timeline.
- Data customer harus menjadi sumber kebenaran untuk relasi bisnis.
- Pipeline sales harus jelas status, owner, nilai peluang, dan next action.
- Aksi penting harus bisa diaudit.
- Integrasi antar project memakai event/contract, bukan akses internal sembarangan.

## Struktur Project Awal

```txt
app/Modules/CRM/
  Leads/
  Contacts/
  Companies/
  Deals/
  Pipelines/
  Activities/
  Notes/
  Tasks/
  CustomerSegments/
  Campaigns/
```

Frontend:

```txt
resources/js/pages/crm/
  leads/
  contacts/
  companies/
  deals/
  pipelines/
  activities/
  notes/
  tasks/
  customer-segments/
  campaigns/
```

## Phase 1: Foundation

Target: data dasar CRM siap dipakai.

Module:

- `Contacts`
- `Companies`
- `Leads`
- `CustomerSegments`
- `Activities`

Fitur:

- Contact profile.
- Company profile.
- Lead capture manual.
- Segment/tag customer.
- Activity timeline untuk call, meeting, email, note.
- Owner assignment.
- Import contact/lead sederhana.

Validasi penting:

- Email contact unik per tenant/project jika nanti multi-tenant.
- Lead tidak boleh duplicate dari email/phone yang sama tanpa warning.
- Company yang sudah punya deal tidak boleh dihapus langsung.
- Activity wajib punya subject, type, actor, dan related record.

## Phase 2: Sales Pipeline

Target: proses sales bisa dipantau dari lead sampai deal won/lost.

Module:

- `Pipelines`
- `Deals`
- `DealStages`
- `Tasks`

Fitur:

- Pipeline board.
- Stage drag-and-drop.
- Deal value dan probability.
- Expected close date.
- Task follow-up.
- Lost reason.
- Convert lead menjadi contact/company/deal.

Validasi penting:

- Deal wajib punya owner.
- Deal stage harus sesuai pipeline.
- Won/lost deal tidak bisa dipindah stage biasa tanpa reopen.
- Lost reason wajib saat deal marked lost.

Event awal:

- `LeadCreated`
- `LeadConverted`
- `DealCreated`
- `DealStageChanged`
- `DealWon`
- `DealLost`

## Phase 3: Customer 360

Target: satu halaman customer menampilkan konteks lengkap.

Module:

- `CustomerProfiles`
- `InteractionTimelines`
- `RelationshipMaps`

Fitur:

- Customer profile 360.
- Timeline gabungan dari note, activity, task, deal, invoice, support ticket.
- Related contacts per company.
- Health score sederhana.
- Last interaction dan next action.

Integrasi event:

- Accounting dispatch `InvoiceIssued` dan `PaymentReceived`.
- Support dispatch `TicketCreated` dan `TicketResolved`.
- CRM listen event untuk timeline customer.

## Phase 4: Marketing & Campaign

Target: CRM mulai mendukung campaign dan segmentasi.

Module:

- `Campaigns`
- `CampaignMembers`
- `Forms`
- `EmailSequences`

Fitur:

- Campaign master.
- Campaign audience dari segment.
- Campaign member status.
- Lead source tracking.
- Form submission mapping ke lead.
- Email sequence placeholder.

Catatan:

- Pengiriman email massal sebaiknya lewat queue.
- Template email bisa integrasi dengan project `Console` Notification Template.

## Phase 5: Sales Performance

Target: sales manager bisa membaca performa tim.

Module:

- `SalesDashboards`
- `Forecasts`
- `Targets`
- `Reports`

Fitur:

- Pipeline value summary.
- Won/lost report.
- Sales forecast.
- Target per user/team.
- Activity report.
- Conversion rate lead ke deal.
- Export report.

Catatan:

- Report membaca data agregat/snapshot jika data membesar.
- Export berat masuk queue.

## Phase 6: Advanced

Target: CRM siap untuk operasional yang lebih kompleks.

Module kandidat:

- `Territories`
- `Teams`
- `SlaRules`
- `DuplicateManagement`
- `CustomerPortal`

Fitur:

- Sales territory.
- Team hierarchy.
- Duplicate merge.
- SLA follow-up.
- Customer portal placeholder.
- Automation rule sederhana.

## Permission Awal

Contoh permission:

```txt
crm.view
crm.dashboard.view
leads.view
leads.create
leads.update
leads.delete
leads.convert
contacts.view
contacts.create
contacts.update
contacts.delete
companies.view
companies.create
companies.update
companies.delete
deals.view
deals.create
deals.update
deals.delete
deals.win
deals.lose
pipelines.manage
activities.view
activities.create
activities.update
reports.view
reports.export
```

Role awal:

- `crm-admin`: akses penuh CRM.
- `sales-manager`: manage pipeline, view report, assign owner.
- `sales-rep`: manage lead, contact, deal miliknya.
- `marketing`: manage campaign dan lead source.
- `crm-viewer`: read-only data CRM.

## UI/UX Arah Awal

CRM sebaiknya terasa cepat, visual, dan action-oriented:

- Dashboard ringkas: pipeline value, hot leads, overdue tasks.
- Table kuat untuk leads, contacts, companies.
- Kanban board untuk deals/pipeline.
- Detail panel kanan untuk preview lead/deal/contact.
- Timeline aktivitas jelas dan mudah discan.
- Quick action: call, email, note, task, meeting.
- Shortcut keyboard untuk create lead, search, fokus pipeline, dan tambah note.

## Urutan Implementasi Yang Disarankan

1. `php artisan make:module Contacts --project=CRM`
2. `php artisan make:module Companies --project=CRM`
3. `php artisan make:module Leads --project=CRM`
4. `php artisan make:module Activities --project=CRM`
5. `php artisan make:module Pipelines --project=CRM`
6. `php artisan make:module Deals --project=CRM`
7. `php artisan make:module Tasks --project=CRM`
8. `php artisan make:module Reports --project=CRM`

## Definition of Done Per Module

- Route, permission, navigation, provider tersedia.
- Policy terpasang.
- FormRequest tersedia untuk aksi mutasi.
- DTO dipakai untuk input service.
- Service berisi use case.
- Transaction membungkus write operation.
- Test route dan permission tersedia.
- Test operasi utama tersedia.
- Audit log untuk aksi penting tersedia.
- Timeline event tercatat untuk aktivitas customer.
- UI sudah dipisah menjadi komponen.
- Empty state, loading state, dan error state tersedia.

## Risiko Yang Perlu Dijaga

- Duplicate customer membuat pipeline kacau.
- Deal stage berubah tanpa audit.
- Sales rep bisa melihat data yang bukan miliknya jika permission kurang ketat.
- Timeline customer tidak lengkap karena event integrasi tidak distandardkan.
- Campaign mengirim email tanpa queue dan rate limit.
- Report sales lambat karena query langsung ke transaksi besar.
