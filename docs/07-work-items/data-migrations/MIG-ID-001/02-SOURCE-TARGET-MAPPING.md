# 02 Pemetaan Sumber ke Target

## Metadata

```yaml
work_item: MIG-ID-001
status: not-started
owner: unassigned
last_updated: 2026-08-13
```

Pemetaan belum dibuat karena target identifier belum disetujui.

Minimum mapping nanti harus mencakup:

- seluruh primary key dan foreign key modul;
- tabel framework (`users`, permission pivots, jobs, media) dan compatibility vendor;
- polymorphic identifiers;
- identifier pada JSON snapshot/event dan file/storage reference;
- route model binding serta external reference;
- mapping old-to-new yang dapat direkonsiliasi.

Tidak ada default atau transformasi yang boleh diasumsikan sebelum inventory lengkap.
