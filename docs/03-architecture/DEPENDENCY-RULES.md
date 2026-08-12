# Aturan Dependensi

## Global aturan

- dependensi point toward stabil abstractions dan eksplisit kontrak.
- module dilarang query module lain boundary's privat tabel secara langsung.
- Internal class adalah tidak publik API.
- siklik boundary dependensi adalah dilarang kecuali ADR dokumen sementara migrasi status.
- Shared Kernel penambahan memerlukan dampak review karena mereka kenaikan coupling.

## aturan registry

| aturan ID | sumber | diizinkan target | dilarang target/access | penegakan |
|---|---|---|---|---|
| `DEP-001` | `<boundary/module>` | `<contract>` | `<internal model/table>` | arsitektur pengujian / review |

## otomatis penegakan

dokumen arsitektur pengujian, static pemeriksaan, dilarang imports, namespace aturan, atau dependensi graf pemeriksaan here.
