# 02 Dampak terhadap Consumer

## Metadata

```yaml
work_item: DEP-HR-001
status: reviewed
owner: unassigned
last_updated: 2026-08-13
```

## Metode Inventaris

Pencarian referensi dilakukan pada file PHP di `app`, `bootstrap`, `config`, `database`, `resources`, `routes`, dan `tests` untuk namespace/nama `IntegrationContracts`.

## Consumer Ditemukan

| Consumer | Jenis | Dampak |
|---|---|---|
| `HRIntegrationSnapshotTest.php` | test | import contract/DTO/guard lama harus dipindah |
| `HRIntegrationComplianceSnapshotTest.php` | test | contract dan DTO contract/document compliance berubah owner |
| `HRIntegrationAssignmentSnapshotTest.php` | test | contract assignment berpindah ke Employees |
| `HRIntegrationEventRegistryTest.php` | test | registry pusat diganti catalog/producer ownership assertion |
| `HRIntegrationEventPrivacyTest.php` | test | privacy assertion wajib dipertahankan |
| `HRIntegrationContractRegistryTest.php` | test | registry/manifest assertion tidak lagi relevan setelah removal |
| module provider/container bindings | runtime internal | binding perlu dipindah ke provider owner |
| command describe/validate/sample | operator/developer | command dihapus atau diganti architecture check terdokumentasi |

## Consumer Tidak Ditemukan

Tidak ditemukan import production di luar folder `HR/IntegrationContracts` yang menggunakan empat provider pusat. Tidak ada route atau navigation pada modul tersebut. Ini adalah bukti search, bukan jaminan tidak ada dynamic string/reflection consumer; pemeriksaan runtime tetap diperlukan setelah bootstrap pulih.

## Konflik Semantics

`EmployeeContractSnapshotProvider` pusat tidak identik dengan `EmployeeContractSnapshotReader` pada `EmployeeContracts`:

- provider pusat dapat fallback ke kontrak terakhir non-cancelled dan menandai `isCurrent=false`;
- reader module-owned hanya memilih kontrak effective-dated berstatus `ACTIVE`/`ENDED` dan mengembalikan `capturedAt`.

Transisi harus memilih compatibility adapter atau mempertahankan kedua semantics dengan nama berbeda. Penggabungan diam-diam dilarang.

## Pemilik Migrasi

Belum ditetapkan. Kandidat owner adalah pemilik masing-masing modul bisnis; tidak ada coding sebelum owner dan acceptance criteria disetujui.
