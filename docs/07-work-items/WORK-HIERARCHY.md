# pekerjaan Hierarchy

Recommended hierarchy:

```text
INITIATIVE
└── ARCHITECTURE / BOUNDARY CHANGE
    ├── MODULE
    │   ├── FEATURE
    │   │   ├── TASK
    │   │   └── BUG / REFACTOR
    │   └── FEATURE
    └── INTEGRATION / MIGRATION
```

## Relationship aturan

- setiap task has exactly satu induk pekerjaan item.
- pekerjaan item boleh depend pada beberapa pekerjaan item tetapi dilarang buat undocumented cycle.
- Emergent pekerjaan adalah linked ke task itu ditemukan itu menggunakan `discovered_by`.
- ditunda child modules remain terdokumentasi sebagai candidates; mereka adalah tidak silently diimplementasikan.

## Pemetaan ke Folder

Hierarchy menjelaskan hubungan bisnis, bukan hierarchy direktori. Setiap feature, bug, refactor, integration, migration, deprecation, incident, atau architecture change yang dikerjakan mempunyai folder sendiri pada kategori jenisnya. Hubungan module, parent, dependency, dan `discovered_by` disimpan sebagai metadata.

Satu module tidak menjadi folder lifecycle permanen yang mencampur semua pekerjaan. Contoh `Console/AccessControls` tetap dapat memiliki beberapa paket terpisah seperti `FTR-ACL-001-*`, `BUG-ACL-002-*`, dan `SEC-ACL-003-*`.
