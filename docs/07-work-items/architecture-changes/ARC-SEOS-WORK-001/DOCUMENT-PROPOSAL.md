---
id: DOC-PROP-001
title: Paket Dokumentasi Kanonis per Pekerjaan
document_type: document-proposal
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: ARC-SEOS-WORK-001
related: [ARC-DDD-LITE-001]
---

# Proposal Paket Dokumentasi Kanonis per Pekerjaan

## Pemicu

Pemilik proyek meminta agar setiap pekerjaan, termasuk pekerjaan pada modul seperti `Console/AccessControls`, mempunyai satu folder di `docs/` yang memuat dokumen pra-kerja dan pascakerja serta memisahkan plan, task, dan backlog.

## Celah Dokumentasi

SEOS telah menyediakan paket berdasarkan jenis pekerjaan, tetapi belum menetapkan secara eksplisit bahwa setiap pekerjaan harus mempunyai folder sendiri. `PLAN.md` dan `BACKLOG.md` belum menjadi artefak wajib pada setiap paket, sedangkan `docs/tasks/*` membentuk daftar global lama yang dapat bertentangan dengan registry dan task lokal.

## Dokumen dan Struktur yang Diusulkan

- nama: Paket dokumentasi kanonis per pekerjaan
- path: `docs/07-work-items/<kategori>/<ID>-<slug>/`
- klasifikasi: reusable
- pemilik: pemilik work item
- fase lifecycle: seluruh fase

Struktur inti:

```text
<ID>-<slug>/
├── WORK-ITEM-README.md
├── PLAN.md
├── TASKS.md
├── BACKLOG.md
├── CONTEXT-PACK.md
├── DEVIATION-RECORD.md
├── EVIDENCE-MANIFEST.md
├── <dokumen pra-kerja khusus jenis>
└── <dokumen pascakerja khusus jenis>
```

Contoh pekerjaan modul:

```text
docs/07-work-items/features/FTR-ACL-001-<slug>/
```

Metadata work item mencatat `affected_boundaries: [Console]` dan `affected_modules: [AccessControls]`; struktur direktori tidak diduplikasi berdasarkan module.

## Tujuan dan Batas

### Wajib memuat

- identitas, klasifikasi, status, owner, hubungan, boundary, dan module;
- fakta, asumsi, risiko, scope, non-scope, dan acceptance criteria;
- plan kontrol pekerjaan;
- task yang dapat diverifikasi dan hanya satu task `in_progress`;
- backlog lokal untuk temuan di luar scope aktif;
- context pack task aktif;
- bukti, deviasi, review, completion, dan baseline sync yang berlaku.

### Dilarang memuat

- status work item yang menyaingi `WORK-ITEM-REGISTRY.md`;
- task aktif yang menyaingi `TASKS.md` lokal;
- backlog yang dianggap otomatis disetujui;
- hasil test, review, atau completion tanpa bukti aktual;
- beberapa pekerjaan independen dalam satu lifecycle folder.

## Input

- klasifikasi perubahan;
- jenis work item;
- boundary/module terdampak;
- requirement, ADR, kontrak, dan bukti kode yang relevan;
- keputusan Human Decision Gate.

## Output

- satu folder kanonis per pekerjaan;
- paket pra-kerja yang memenuhi Definition of Ready;
- paket pascakerja yang memenuhi Definition of Done;
- temuan baru yang dapat dipromosikan dari backlog menjadi work item terdaftar.

## Hubungan dengan Sumber Kebenaran

1. `WORK-ITEM-REGISTRY.md` adalah sumber status work item.
2. `WORK-ITEM-README.md` adalah identitas dan scope paket.
3. `PLAN.md` adalah urutan kontrol pekerjaan.
4. `TASKS.md` adalah sumber status task dalam paket.
5. `BACKLOG.md` hanya menampung kandidat tindak lanjut, bukan approval atau status aktif.
6. Dokumen jenis pekerjaan menyimpan keputusan dan bukti lifecycle khusus.
7. `docs/tasks/*` menjadi historis/superseded dan tidak lagi mengendalikan pekerjaan.

## Alternatif yang Dipertimbangkan

### Folder permanen per module

Ditolak sebagai lokasi kanonis karena mencampur beberapa lifecycle pekerjaan dan menyulitkan klasifikasi lintas jenis. Boundary/module tetap dicatat sebagai metadata.

### Daftar global plan/task/backlog

Ditolak sebagai sumber aktif karena memisahkan task dari scope, keputusan, bukti, dan completion work item.

### Paket berdasarkan jenis tanpa artefak inti wajib

Ditolak karena komposisinya tidak konsisten dan mudah kehilangan backlog atau plan lokal.

## Risiko Membuat Struktur

- tambahan dokumen untuk perubahan kecil;
- template bersama dapat menyimpang jika tidak disinkronkan;
- pemilik pekerjaan dapat menduplikasi status dari registry.

Mitigasi: paket `TRIVIAL` boleh ringkas, tetapi tetap memiliki folder dan artefak inti; peran setiap artefak dibuat normatif; verifikasi dokumentasi memeriksa duplikasi status.

## Risiko Jika Tidak Dibuat

- pekerjaan tidak mempunyai rekam pra/pasca yang utuh;
- task dan backlog global terus bertentangan dengan registry;
- konteks dan bukti mudah terpisah dari keputusan asal;
- pekerjaan lintas sesi sulit direview atau dilanjutkan.

## Standar dan Indeks yang Diperbarui

- governance lifecycle, classification, documentation standard, creation policy, dan context policy;
- `docs/07-work-items/README.md`, hierarchy, registry, dan template shared;
- README setiap paket jenis pekerjaan;
- daftar `docs/tasks/*` lama ditandai superseded.

## Keputusan

```yaml
status: approved
approved_by: Pemilik proyek
date: 2026-08-13
notes:
  - berlaku untuk pekerjaan berikutnya setelah sinkronisasi template
  - perilaku aplikasi tidak berubah
  - backlog lokal tidak menggantikan registry
evidence:
  - instruksi eksplisit untuk membuat dan langsung menyetujui proposal
```
