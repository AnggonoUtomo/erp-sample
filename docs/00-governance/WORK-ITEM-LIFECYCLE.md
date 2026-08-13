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
affected_boundaries: []
affected_modules: []
```

## Aturan Folder dan Artefak

1. Setiap work item mempunyai tepat satu folder di `docs/07-work-items/<kategori>/<ID>-<slug>/`.
2. Folder dibuat ketika pekerjaan diregistrasi dan sebelum implementasi.
3. `PLAN.md`, `TASKS.md`, dan `BACKLOG.md` wajib hadir dan mempunyai peran berbeda.
4. Dokumen pra-kerja harus lengkap sebelum transisi `approved → ready`.
5. Dokumen pascakerja dan evidence harus aktual sebelum transisi `verified → completed`.
6. Item backlog yang dipilih untuk dikerjakan harus diklasifikasikan, diberi ID, diregistrasi, dan mempunyai paket sendiri.
7. Satu folder tidak boleh menampung beberapa pekerjaan independen hanya karena boundary/module-nya sama.
