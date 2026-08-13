# Work Item

Direktori ini menyimpan lifecycle, keputusan, task, backlog lokal, dan bukti setiap pekerjaan.

## Lokasi Kanonis

Setiap pekerjaan mempunyai tepat satu folder:

```text
docs/07-work-items/<kategori>/<ID>-<slug>/
```

Kategori yang disetujui:

```text
features/
architecture-changes/
bugs/
refactorings/
integrations/
data-migrations/
dependency-upgrades/
security-changes/
performance-improvements/
deprecations/
incidents/
```

Contoh pekerjaan pada `Console/AccessControls`:

```text
docs/07-work-items/features/FTR-ACL-001-<slug>/
```

Metadata paket mencatat:

```yaml
affected_boundaries: [Console]
affected_modules: [AccessControls]
```

Folder tidak dikelompokkan ulang per module karena satu pekerjaan dapat lintas module dan satu module dapat mempunyai banyak lifecycle pekerjaan.

## Komposisi Paket

Mulai dari `templates/shared/`, lalu tambahkan seluruh dokumen dari template jenis pekerjaan. Jika nama file bertabrakan, template jenis pekerjaan yang lebih spesifik berlaku.

Artefak inti yang wajib hadir:

```text
WORK-ITEM-README.md
PLAN.md
TASKS.md
BACKLOG.md
CONTEXT-PACK.md
DEVIATION-RECORD.md
EVIDENCE-MANIFEST.md
REVIEW-REPORT.md
COMPLETION-REPORT.md
```

Dokumen jenis pekerjaan menambah specification, design, assessment, test plan, validation, removal report, atau artefak lain sesuai risikonya. Jika paket jenis sudah mempunyai review/completion khusus, dokumen khusus itu menggantikan template generik.

## Peran Sumber Kebenaran

- `WORK-ITEM-REGISTRY.md`: status work item.
- `WORK-ITEM-README.md`: identitas dan scope.
- `PLAN.md`: urutan kontrol, checkpoint, risiko, dan rollback.
- `TASKS.md`: status task lokal; hanya satu `in_progress`.
- `BACKLOG.md`: kandidat tindak lanjut; bukan approval.
- `CONTEXT-PACK.md`: konteks eksekusi task aktif.
- evidence/review/completion: bukti pascakerja.

`docs/tasks/*` tidak lagi menjadi sumber status aktif.

Temuan material hanya boleh singgah di backlog selama triage. Setelah klasifikasinya diketahui, temuan wajib diberi ID, diregistrasi, dan mempunyai paket sendiri sebelum task terdampak dilanjutkan.

## Prosedur

1. Temukan dan klasifikasikan pekerjaan.
2. Beri ID, daftarkan pada registry, dan buat satu folder kanonis.
3. Salin template shared dan template jenis pekerjaan.
4. Isi metadata boundary/module, fakta, asumsi, risiko, scope, acceptance, plan, tasks, dan backlog.
5. Dapatkan approval dan penuhi Definition of Ready.
6. Pilih satu task aktif dan siapkan context pack.
7. Implementasikan satu vertical slice terkecil.
8. Isi evidence, deviation, review, completion, dan baseline sync.
9. Penuhi Definition of Done sebelum `completed`.
10. Arsipkan melalui registry dan referensi release tanpa menghapus riwayat.
