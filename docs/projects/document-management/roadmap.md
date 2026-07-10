# Roadmap Project Document Management

Roadmap ini adalah rencana awal untuk membangun project `DocumentManagement` di atas starterkit modular. Fokusnya adalah mengelola dokumen, folder, versi, akses, approval, dan pencarian dokumen yang dapat dipakai lintas project seperti accounting, CRM, HR, legal, dan operations.

## Prinsip Utama

- Dokumen harus punya ownership dan permission yang jelas.
- Versi dokumen tidak boleh hilang.
- Aktivitas dokumen harus bisa diaudit.
- File storage harus dipisahkan dari metadata bisnis.
- Integrasi antar project memakai reference/contract, bukan hard dependency ke internal module.

## Struktur Project Awal

```txt
app/Modules/DocumentManagement/
  Documents/
  Folders/
  DocumentVersions/
  DocumentCategories/
  Tags/
  Shares/
  Approvals/
  RetentionPolicies/
  SearchIndexes/
```

Frontend:

```txt
resources/js/pages/document-management/
  documents/
  folders/
  document-versions/
  document-categories/
  tags/
  shares/
  approvals/
  retention-policies/
  search-indexes/
```

## Phase 1: Foundation

Target: upload, folder, metadata, dan akses dasar siap.

Module:

- `Documents`
- `Folders`
- `DocumentCategories`
- `Tags`
- `DocumentVersions`

Fitur:

- Upload dokumen dengan Spatie Media Library.
- Folder tree.
- Document metadata: title, description, category, tags, owner.
- Versioning awal.
- Preview metadata.
- Download file.
- Soft delete.

Validasi penting:

- File wajib punya owner.
- Extension dan ukuran file mengikuti policy.
- Folder tidak boleh menjadi child dari dirinya sendiri.
- Dokumen yang punya versi tidak boleh dihapus permanen tanpa permission khusus.

## Phase 2: Access & Sharing

Target: akses dokumen bisa dikontrol dengan aman.

Module:

- `Shares`
- `AccessRules`
- `DocumentPermissions`

Fitur:

- Share dokumen ke user/role.
- Share folder ke user/role.
- Permission: view, download, upload version, approve, delete.
- Public link optional dengan expiry.
- Password protected link optional.
- Revoke share.

Validasi penting:

- Public link harus punya expiry jika policy mewajibkan.
- User tanpa akses folder tidak otomatis boleh akses dokumen private.
- Permission delete tidak sama dengan permission archive.

Event awal:

- `DocumentUploaded`
- `DocumentShared`
- `DocumentDownloaded`
- `DocumentVersionUploaded`
- `DocumentArchived`

## Phase 3: Approval Workflow

Target: dokumen penting bisa melewati review/approval.

Module:

- `Approvals`
- `ApprovalSteps`
- `ApprovalRequests`

Fitur:

- Submit document for review.
- Multi-step approval.
- Approve/reject dengan catatan.
- Request changes.
- Approval history.
- Lock document saat approval berjalan.

Validasi penting:

- Approver tidak boleh approve step miliknya jika policy melarang self-approval.
- Dokumen locked tidak bisa upload versi baru kecuali request changes.
- Rejected document harus punya alasan.

## Phase 4: Search & Discovery

Target: dokumen mudah ditemukan.

Module:

- `SearchIndexes`
- `SavedSearches`
- `RecentDocuments`

Fitur:

- Search by title, category, tag, owner, date.
- Filter folder/category/tag.
- Recent documents.
- Favorite documents.
- Saved search.
- Full-text search placeholder.

Catatan:

- Indexing file besar sebaiknya lewat queue.
- OCR dapat menjadi phase lanjutan, bukan foundation.

## Phase 5: Retention & Compliance

Target: lifecycle dokumen bisa dikontrol.

Module:

- `RetentionPolicies`
- `Archives`
- `LegalHolds`

Fitur:

- Retention policy per category.
- Archive document.
- Restore archived document.
- Legal hold agar dokumen tidak bisa dihapus.
- Expiry reminder.
- Deletion approval.

Validasi penting:

- Dokumen legal hold tidak boleh dihapus.
- Retention deletion harus masuk audit log.
- Restore archive harus mempertahankan versi dan audit trail.

## Phase 6: Advanced

Target: document management siap untuk skala lebih besar.

Module kandidat:

- `Templates`
- `OcrJobs`
- `DigitalSignatures`
- `ExternalStorage`
- `DocumentRequests`

Fitur:

- Document template.
- OCR queue.
- Digital signature placeholder.
- External storage adapter.
- Request document ke user.
- Bulk upload.
- Duplicate detection.

## Permission Awal

Contoh permission:

```txt
document-management.view
documents.view
documents.upload
documents.update
documents.delete
documents.download
documents.archive
documents.restore
documents.force-delete
document-versions.view
document-versions.upload
folders.view
folders.create
folders.update
folders.delete
shares.create
shares.revoke
approvals.view
approvals.submit
approvals.approve
approvals.reject
retention-policies.manage
```

Role awal:

- `document-admin`: akses penuh document management.
- `document-manager`: manage folder, category, share, approval.
- `document-contributor`: upload dan update dokumen miliknya.
- `document-reviewer`: approve/reject dokumen.
- `document-viewer`: read-only sesuai share/access.

## UI/UX Arah Awal

Document Management sebaiknya terasa seperti workspace file yang rapi:

- Layout dua atau tiga panel: folder tree, list dokumen, detail preview.
- Table/list toggle untuk dokumen.
- Drag-and-drop upload.
- Badge status: Draft, In Review, Approved, Archived, Locked.
- Detail panel kanan untuk metadata, versi, share, dan audit.
- Breadcrumb folder jelas.
- Quick filter untuk recent, favorites, shared with me, archived.
- Shortcut keyboard untuk upload, search, fokus folder, fokus list, dan download.

## Urutan Implementasi Yang Disarankan

1. `php artisan make:module Folders --project=DocumentManagement`
2. `php artisan make:module DocumentCategories --project=DocumentManagement`
3. `php artisan make:module Tags --project=DocumentManagement`
4. `php artisan make:module Documents --project=DocumentManagement`
5. `php artisan make:module DocumentVersions --project=DocumentManagement`
6. `php artisan make:module Shares --project=DocumentManagement`
7. `php artisan make:module Approvals --project=DocumentManagement`
8. `php artisan make:module RetentionPolicies --project=DocumentManagement`

## Definition of Done Per Module

- Route, permission, navigation, provider tersedia.
- Policy terpasang.
- FormRequest tersedia untuk aksi mutasi.
- DTO dipakai untuk input service.
- Service berisi use case.
- Transaction membungkus write operation.
- Test route dan permission tersedia.
- Test operasi utama tersedia.
- Audit log untuk upload, download, share, approval, dan delete tersedia.
- Media collection terdefinisi jelas.
- UI sudah dipisah menjadi komponen.
- Empty state, loading state, dan error state tersedia.

## Risiko Yang Perlu Dijaga

- File bisa diakses tanpa permission karena URL storage terbuka.
- Versi dokumen tertimpa tanpa audit.
- Folder permission tidak konsisten dengan document permission.
- Public link tidak punya expiry.
- File besar membuat request timeout karena upload/processing tidak dipisah.
- Metadata dokumen tidak konsisten antar project karena tidak ada contract.
