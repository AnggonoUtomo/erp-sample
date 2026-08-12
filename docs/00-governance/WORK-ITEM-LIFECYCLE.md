# pekerjaan item lifecycle

## Status

`discovered → proposed → approved → ready → in_progress → implemented → verified → completed → archived`

opsional states: `blocked`, `deferred`, `rejected`, `cancelled`, `superseded`.

## transisi aturan

| dari | untuk | wajib bukti |
|---|---|---|
| ditemukan | diusulkan | Discovery/problem catat |
| diusulkan | disetujui | Ruang Lingkup, dampak, keputusan pemilik |
| disetujui | siap | definisi of Ready terpenuhi |
| siap | in_progress | aktif task selected dan konteks paket prepared |
| in_progress | diimplementasikan | kode/config/docs dihasilkan; tidak ada penyelesaian klaim belum |
| diimplementasikan | diverifikasi | wajib pengujian/pemeriksaan lulus |
| diverifikasi | selesai | review, penyelesaian laporan, baseline sync |
| selesai | archived | rilis/referensi dicatat |

dilarang shortcuts sertakan `proposed → in_progress`, `implemented → completed`, dan `blocked → completed`.

## Metadata

setiap work item induk dokumen wajib memuat:

```yaml
id: WI-AREA-NNN
kind: feature
classification: STANDARD
status: proposed
owner: unassigned
created_at: YYYY-MM-DD
updated_at: YYYY-MM-DD
parent: null
depends_on: []
blocks: []
related_adrs: []
```
