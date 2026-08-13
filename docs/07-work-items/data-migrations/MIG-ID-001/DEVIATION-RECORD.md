# Catatan Deviasi — MIG-ID-001

```yaml
id: DEV-ID-001
work_item: MIG-ID-001
status: open
requires_adr: true
```

## Pendekatan Awal

ULID digabung ke update generator dan restrukturisasi DDD-Lite.

## Deviasi

Migrasi identifier dipisahkan sebagai data migration CRITICAL dan ditunda.

## Alasan

Kode aktual memakai bigint; perubahan lintas tabel membutuhkan lifecycle, approval, dan rollback tersendiri.

## Persetujuan

Pemilik proyek menyetujui pemisahan scope pada 2026-08-13. Persetujuan tersebut bukan approval untuk menjalankan migrasi.
