# ARC-DDD-LITE-001 — Restrukturisasi Struktur Modul ke DDD-Lite Layers

```yaml
id: ARC-DDD-LITE-001
kind: architecture-change
classification: CRITICAL
status: proposed
owner: unassigned
created_at: 2026-08-12
updated_at: 2026-08-12
parent: null
discovered_by: null
depends_on: []
blocks: []
related_adrs: []
```

## Tujuan

Mengonversi struktur folder modul dari flat structure ke DDD-Lite layered structure sesuai acuan `docs/DDD-Lite-Modular-Monolith-Laravel-Acuan.md`. Restrukturisasi ini bertujuan untuk:

1. Menetapkan pola arsitektur yang konsisten di semua modul.
2. Memisahkan tanggung jawab setiap layer (Application, Domain, Infrastructure, Presentation).
3. Memudahkan penelusuran kode dan maintenance.
4. Menyiapkan fondasi untuk evolusi arsitektur selanjutnya.

## Scope / Non-Scope

### Scope
- Restrukturisasi folder semua modul HR (16 modul) dan DocumentManagement.
- Update module generator (`MakeModuleCommand`) untuk generate struktur DDD-Lite.
- Migrasi namespace di semua file yang terdampak.
- Migrasi tests dari `tests/` ke dalam tiap modul.
- Migrasi routes dari `routes/` ke dalam tiap modul.
- Update autoloader dan konfigurasi.

### Non-Scope
- Mengubah logika bisnis.
- Menambah fitur baru.
- Mengubah kontrak antar-modul yang sudah ada.
- Refactoring kode di luar struktur folder.

## Indeks Dokumen

| Dokumen | Path | Status |
|---|---|---|
| Discovery Record | `01-DISCOVERY-RECORD.md` | draft |
| Boundary Proposal | `02-BOUNDARY-PROPOSAL.md` | draft |
| Impact Assessment | `03-IMPACT-ASSESSMENT.md` | draft |
| Implementation Plan | `04-IMPLEMENTATION-PLAN.md` | draft |
| Validation Report | `05-VALIDATION-REPORT.md` | draft |
| Completion Report | `06-COMPLETION-REPORT.md` | draft |
| Context Pack | `CONTEXT-PACK.md` | draft |
| Deviation Record | `DEVIATION-RECORD.md` | — |
| Evidence Manifest | `EVIDENCE-MANIFEST.md` | — |

## Keputusan Saat Ini / Aksi Berikutnya

- [x] Dokumen work item dibuat
- [ ] Isi Discovery Record
- [ ] Isi Boundary Proposal
- [ ] Isi Impact Assessment
- [ ] Isi Implementation Plan
- [ ] Human Decision Gate approval
- [ ] Mulai implementasi

</contents>