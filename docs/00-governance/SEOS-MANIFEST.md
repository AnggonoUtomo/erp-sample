# SEOS manifest

## Identitas

- nama: software engineering Operating sistem (SEOS)
- versi: 2.0.0
- Tujuan: AI-friendly, repository-local operating sistem untuk perencanaan, implementing, validating, releasing, dan evolving software.

## Core Principles

1. dokumentasi adalah executable panduan, tidak decoration.
2. setiap material kode perubahan maps ke classified pekerjaan item.
3. pekerjaan berjalan melalui eksplisit pra-pengerjaan, implementasi, dan pasca-pengerjaan bukti.
4. baseline dokumen jelaskan saat ini kebenaran; work item dokumen pertahankan perubahan riwayat.
5. AI boleh propose perubahan, tetapi human gate lindungi high-impact keputusan.
6. satu aktif task, terbatas konteks, terukur kriteria penerimaan, verifiable bukti.
7. setiap pekerjaan mempunyai tepat satu folder work item kanonis yang menyatukan pra-kerja, plan, task, backlog lokal, konteks, dan pascakerja.

## Paket Pekerjaan Kanonis

Lokasi paket mengikuti jenis pekerjaan:

```text
docs/07-work-items/<kategori>/<ID>-<slug>/
```

Boundary dan module terdampak dicatat pada metadata paket, bukan dengan membuat sumber status paralel per module. `WORK-ITEM-REGISTRY.md` mengendalikan status work item, sedangkan `TASKS.md` di dalam paket mengendalikan status task. `BACKLOG.md` hanya memuat kandidat tindak lanjut yang belum disetujui.

## Canonical lifecycle

`DISCOVER → CLASSIFY → PROPOSE → APPROVE → READY → IMPLEMENT → VERIFY → REVIEW → COMPLETE → BASELINE-SYNC → ARCHIVE`

## Prioritas Sumber Kebenaran

1. disetujui aktif pekerjaan item dan task
2. diterima ADRs dan boundary kontrak
3. Saat Ini baseline arsitektur/design dokumen
4. engineering dan kualitas standar
5. eksisting implementasi
6. historis work item dokumen

ketika ini disagree, hentikan dan buat deviation atau keputusan catat. tidak silently pilih.
