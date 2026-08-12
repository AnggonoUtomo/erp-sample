# dokumen pembuatan dan evolusi kebijakan

## Tujuan

Ini kebijakan mengatur ketika AI atau developer boleh buat baru dokumen selama development. baseline dokumentasi adalah extensible, tetapi -nya structure wajib berkembang deliberately dan traceably.

## Core aturan

AI boleh buat baru proyek dokumen hanya ketika eksisting disetujui template tidak dapat represent baru ditemukan concern tanpa becoming ambigu, terlalu padat, atau tidak dapat ditelusuri.

AI dilarang menciptakan paralel dokumentasi sistem, rename baseline directories, atau buat sembarang top-level folder.

## Permitted case

 baru dokumen adalah permitted ketika at setidaknya satu kondisi berlaku:

1. baru terbatas konteks, module boundary, submodule, integrasi, keamanan model, migrasi, atau operasional tanggung jawab adalah ditemukan.
2. disetujui fitur exposes missing arsitektural keputusan atau cross-module kontrak.
3. pekerjaan needs terpisah pre-implementation dan post-implementation bukti.
4. dokumen akan jika tidak mencampur tidak terkait lifecycle states atau tanggung jawab.
5. berulang dokumen jenis muncul di at setidaknya dua pekerjaan item dan layak reusable template.

## wajib proses

sebelum membuat baru dokumen jenis atau structure:

1. Search baseline template dan eksisting proyek dokumen.
2. catat mengapa eksisting template adalah insufficient.
3. klasifikasikan perubahan sebagai khusus proyek dokumen atau reusable template.
4. buat `DOC-PROPOSAL` menggunakan `docs/07-work-items/templates/DOCUMENT-PROPOSAL.md`.
5. untuk arsitektur perubahan, buat Arsitektur perubahan paket.
6. perbarui `DOCUMENTATION-STANDARD.md` dan relevant indexes jika proposal adalah disetujui.
7. pertahankan stabil ID dan tautan dari impacted dokumen.

## khusus proyek dokumen

 khusus proyek dokumen describes satu konkret concern, untuk contoh:

```text
docs/03-architecture/boundaries/BC-ACL-001-access-control/
```

itu tidak secara otomatis become reusable template.

## reusable template

 baru reusable template boleh menjadi ditambahkan hanya ketika:

- structure adalah diharapkan ke menjadi reused;
- -nya tujuan tidak tumpang tindih eksisting template;
- wajib lifecycle input dan Output adalah didefinisikan;
- -nya pembuatan aturan adalah terdokumentasi;
- itu has stabil location di bawah `docs/07-work-items/templates/`.

## Metadata Wajib

setiap baru terkelola dokumen wajib sertakan:

```yaml
id:
title:
document_type:
status: draft
version: 0.1.0
owner:
created:
updated:
source_work_item:
related: []
```

## Larangan perilaku

- membuat baru arsitektur folder sementara coding tanpa pausing task.
- Menganggap implementasi penemuan sebagai disetujui requirement.
- menyembunyikan scope pertambahan di dalam eksisting task.
- membuat duplicate catalogs atau competing sumber sumber kebenaran.
- memperbarui kode pertama dan documenting keputusan afterward, kecuali darurat perbaikan secara eksplisit dicatat sebagai deviation.
