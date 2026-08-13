# Catatan Deviasi — ARC-DDD-LITE-001

## Metadata

```yaml
work_item: ARC-DDD-LITE-001
status: open
owner: unassigned
last_updated: 2026-08-13
```

| ID | Rencana awal | Keputusan terkini | Alasan | Persetujuan |
|---|---|---|---|---|
| `DEV-ARC-001` | Delapan layer/folder penuh pada semua modul | Lokasi baku, folder minimal sesuai kebutuhan | Mencegah over-engineering dan sesuai DDD-Lite | ADR-0001 accepted |
| `DEV-ARC-002` | ULID digabung dengan generator/restrukturisasi | ULID dipisah ke `MIG-ID-001` dan deferred | Risiko data dan rollback berbeda | Pemilik proyek, 2026-08-13 |
| `DEV-ARC-003` | `HR/IntegrationContracts` direstrukturisasi sebagai modul target | Shell dideprecate; kontrak pindah ke owner bisnis setelah readiness | Modul tidak memiliki tanggung jawab bisnis mandiri | ADR-0002 accepted |
| `DEV-ARC-004` | Generator tersedia sebagai phase pertama | File command sempat hilang lalu dipulihkan exact dari commit sumber | Fakta repository berbeda dari rencana | restore disetujui Pemilik proyek, 2026-08-13 |
| `DEV-ARC-005` | Restore langsung diverifikasi dengan local diff dan test | Percobaan awal timeout; setelah runner pulih, pemeriksaan diulang satu per satu dan semuanya lulus | Gangguan execution environment sementara | resolved, 2026-08-13 |

Tidak ada implementasi yang ditulis ulang untuk menyembunyikan deviasi ini.
