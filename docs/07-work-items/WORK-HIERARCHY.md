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
