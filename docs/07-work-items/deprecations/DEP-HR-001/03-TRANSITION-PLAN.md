# 03 Rencana Transisi

## Metadata

```yaml
work_item: DEP-HR-001
status: proposed
owner: unassigned
last_updated: 2026-08-13
```

## Urutan Transisi

1. ADR-0002 diterima dan owner bisnis tiap contract ditetapkan.
2. Capture behavior fixture untuk keempat provider dan privacy guard.
3. Tambahkan/migrasikan contract dan DTO pada owner target tanpa mengubah hasil.
4. Pindahkan binding ke service provider owner.
5. Migrasikan test satu kelompok contract pada satu waktu.
6. Ganti registry runtime dengan catalog/architecture assertion yang relevan.
7. Search namespace lama dan jalankan runtime/container verification.
8. Masuk readiness review.
9. Hapus manifest, provider, command, permissions, dan folder shell hanya setelah gate approved.

## Compatibility

Keputusan antara alias/shim sementara dan cut-over langsung belum dibuat. Search menunjukkan tidak ada production import eksternal modul, tetapi dynamic consumer belum dapat dibuktikan karena artisan bootstrap rusak. Pilihan dibuat setelah tooling valid.

## Telemetry/Bukti

- container resolution untuk setiap contract;
- hasil fixture sebelum/sesudah;
- zero import namespace lama;
- module list tanpa shell;
- seluruh test integrasi HR terkait;
- privacy assertion dan schema snapshot.

## Rollback

Rollback mengembalikan namespace/binding shell pada commit slice terakhir. Tidak ada migration database atau data mutation dalam work item ini.

## Tenggat

Tidak ada tanggal removal sebelum scope dan owner disetujui. Ketiadaan deadline lebih aman daripada deadline fiktif.
