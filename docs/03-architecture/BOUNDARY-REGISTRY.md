# registry Boundary

| ID | Boundary | Tujuan | Pemilik | status | path | ADR |
|---|---|---|---|---|---|---|
| `<BC-ID>` | `<Name>` | `<Capability>` | `<Pemilik>` | candidate/aktif/stable/deprecated/retired | `boundaries/...` | `<ADR>` |

## Aturan

- setiap arsitektural boundary has stabil ID.
- Turunan modules adalah registered di boundary's `MODULE-CATALOG.md`.
- lintas-boundary akses wajib gunakan disetujui kontrak, event, atau terdokumentasi integrasi mechanism.
- boundary boleh tidak mengekspos internal database model sebagai -nya publik kontrak.
