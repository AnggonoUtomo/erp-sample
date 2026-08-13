# 01 Proposal Migrasi

## Metadata

```yaml
work_item: MIG-ID-001
status: deferred
owner: unassigned
last_updated: 2026-08-13
```

## Kondisi Sumber

Primary key relasional utama menggunakan `$table->id()` dan foreign key menggunakan `foreignId()`. Model/service/test juga mengasumsikan identifier integer pada banyak signature.

## Target

Belum diputuskan. Dokumen lama yang menulis `CHAR(26) ULID` bukan persetujuan migrasi dan tidak menjadi target authoritative.

## Alasan Pemisahan

- perubahan data bersifat lintas hampir semua tabel;
- foreign key, polymorphic key, route model binding, DTO, event, backup/restore, dan integrasi ikut terdampak;
- rollback jauh lebih sulit daripada pemindahan namespace;
- memerlukan volume data dan rehearsal yang belum tersedia.

## Gate

Human approval wajib sebelum target identifier, compatibility strategy, atau implementation plan dianggap approved.
