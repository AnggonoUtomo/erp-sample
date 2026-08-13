# Catatan Deviasi — DEP-HR-001

```yaml
id: DEV-HR-001
work_item: DEP-HR-001
status: open
requires_adr: true
```

## Pendekatan Awal

`HR/IntegrationContracts` akan ikut direstrukturisasi sebagai salah satu modul DDD-Lite.

## Kondisi Aktual

Modul tidak mempunyai capability bisnis mandiri dan menduplikasi pola integration surface yang sudah dimiliki modul sumber.

## Deviasi

Shell diusulkan untuk dideprecate; contract bernilai dipindahkan ke owner bisnis.

## Dampak

Namespace/binding/test berubah, tetapi behavior snapshot, versioning, dan privacy tetap dipertahankan.

## Keputusan

ADR-0002 telah accepted. Implementasi tetap menunggu behavior baseline, keputusan compatibility, readiness checklist, dan removal gate.
