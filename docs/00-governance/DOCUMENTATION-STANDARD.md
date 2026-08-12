# Standar Dokumentasi

## Tujuan

Menetapkan aturan bagaimana dokumentasi proyek dibuat, diberi identitas, disetujui, diubah, dipelihara, dan dihubungkan dengan implementasi kode.

## Metadata Dokumen

Setiap dokumen yang dikelola SEOS sebaiknya memiliki metadata yang relevan, misalnya:

```yaml
id: DOC-AREA-NNN
status: draft
owner: unassigned
created: YYYY-MM-DD
updated: YYYY-MM-DD
related: []
```

## Status Dokumen

- `draft` — masih disusun dan belum menjadi sumber kebenaran.
- `proposed` — siap untuk direview dan diputuskan.
- `approved` — telah disetujui dan berlaku sebagai sumber kebenaran sesuai scope-nya.
- `superseded` — digantikan dokumen atau keputusan yang lebih baru.
- `archived` — dipertahankan untuk kebutuhan historis dan tidak lagi berlaku.

## Aturan Penulisan

- Gunakan pernyataan eksplisit dan dapat diuji bila memungkinkan.
- Bedakan fakta, asumsi, keputusan, risiko, pertanyaan terbuka, dan rekomendasi.
- Hindari duplikasi sumber kebenaran.
- Gunakan ID stabil untuk requirement, aturan bisnis, keputusan, fitur, task, dan pengujian.
- Hubungkan dokumen ke work item, ADR, kontrak, atau bukti implementasi yang relevan.
- Jangan mengubah dokumen historis agar terlihat seolah rencana awal selalu sesuai dengan implementasi akhir; gunakan Deviation Record.

## Kebijakan Bahasa Dokumentasi

**Bahasa Indonesia adalah bahasa wajib untuk seluruh dokumentasi SEOS dan seluruh dokumentasi proyek yang menggunakan baseline ini.**

Aturan berikut bersifat normatif:

- Gunakan Bahasa Indonesia untuk narasi, penjelasan, heading deskriptif, requirement, kriteria penerimaan, keputusan, risiko, checklist, laporan, ADR, runbook, dan catatan work item.
- Istilah teknis yang sudah umum atau kehilangan ketepatan jika diterjemahkan boleh dipertahankan, misalnya `API`, `event`, `boundary`, `module`, `repository`, `commit`, `pull request`, `rollback`, `runtime`, `deployment`, dan nama pola arsitektur.
- Nama library, framework, protocol, command, path, identifier kode, schema field, enum, nama class/function, dan kutipan sumber resmi tidak wajib diterjemahkan.
- Jika istilah asing berpotensi ambigu, jelaskan maknanya dalam Bahasa Indonesia pada penggunaan pertama.
- AI dan developer dilarang membuat dokumentasi baru berbahasa Inggris kecuali format tersebut diwajibkan oleh tool, standard, atau vendor eksternal. Alasan pengecualian wajib dicatat.
- Dokumen lama yang masih berbahasa Inggris wajib diterjemahkan ketika disentuh atau direvisi.
- Jika terjadi konflik gaya bahasa, Bahasa Indonesia menjadi default dan sumber kebenaran dokumentasi proyek.

## Pengendalian Perubahan

Ketika scope atau perilaku sistem berubah:

1. Perbarui requirement atau specification yang mengatur perubahan tersebut.
2. Catat alasan dan dampaknya.
3. Buat atau perbarui ADR jika arsitektur terdampak.
4. Perbarui task dan kriteria penerimaan yang terdampak.
5. Perbarui traceability.
6. Lanjutkan implementasi hanya setelah approval dan readiness yang diperlukan terpenuhi kembali.

## Evolusi Struktur Dokumentasi

Baseline boleh berkembang secara terkendali melalui `DOCUMENT-CREATION-POLICY.md`. Dokumen project-specific baru wajib ditempatkan pada kategori yang sudah dikelola. Template reusable baru memerlukan `DOCUMENT-PROPOSAL.md` dan persetujuan. Evolusi arsitektur menggunakan paket `architecture-change`.

## Review Berkala

Review dokumen baseline pada milestone atau release utama, dan review dokumen work item sebelum implementasi serta sebelum pekerjaan dinyatakan selesai.
