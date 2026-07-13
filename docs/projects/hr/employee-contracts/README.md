# Employee Contracts

Paket dokumen ini mendefinisikan module `HR/EmployeeContracts` sebelum implementasi dimulai. Module mengelola masa berlaku hubungan kontraktual employee secara historis dan menjadi salah satu input snapshot HR untuk Payroll.

## Status

`Implementation complete` — ADR-001/002 dan Task 01–08 telah selesai. Module contract, lifecycle, archive/restore, expiry query, dan snapshot integration v1 telah tersedia.

## Expiry query

Gunakan command read-only berikut untuk daftar kontrak aktif yang berakhir dalam window inklusif:

```bash
php artisan hr:contracts-expiring --date=2026-07-13 --within=30
```

`--date` harus berformat `YYYY-MM-DD`; jika tidak diberikan, command memakai tanggal hari ini. `--within` adalah jumlah hari non-negatif dan default `30`. Exit code `0` berarti query valid, termasuk ketika hasil kosong; exit code `1` berarti opsi tanggal/window tidak valid. Command tidak mengubah contract, audit log, atau mengirim notification.

## Urutan baca

1. [Specification](specification.md) — tujuan, requirement, non-scope, data contract, acceptance criteria, dan test plan.
2. [ADR-001: Effective-dated contracts](decisions/001-effective-dated-contracts.md) — alasan kontrak dimodelkan sebagai interval waktu immutable secara historis.
3. [ADR-002: Versioned snapshot boundary](decisions/002-versioned-snapshot-boundary.md) — bentuk snapshot v1 dan larangan akses model HR oleh consumer.
4. [Implementation plan](implementation-plan.md) — urutan vertical slice, dependency, risiko, dan checkpoint.
5. [Tasks](tasks.md) — unit kerja kecil lengkap dengan file, acceptance criteria, dan cara test.

## Dokumen terkait

- [HR roadmap](../roadmap.md) — posisi Employee Contracts dalam Phase 3 HR.
- [HR module guide](../module-guide.md) — aturan folder, permission, backend, frontend, dan quality gates.
- [Data lifecycle](../../../architecture/data-lifecycle.md) — prinsip soft delete dan histori.
- [Payroll planning](../../../planning/payroll.md) — Payroll mengonsumsi snapshot/contract, bukan model internal HR.
- [Module contract ADR](../../../reviews/2026-07-11-project-baseline/decisions/002-contract-as-source-of-truth.md) — manifest module adalah sumber contract runtime.

## Gate sebelum coding

Implementasi hanya dimulai setelah manusia menyetujui:

- aturan overlap dan kontrak tanpa tanggal akhir;
- lifecycle draft, active, ended, cancelled, dan superseded;
- permission untuk data kompensasi atau field sensitif;
- kontrak integrasi yang boleh dibaca Payroll;
- scope vertical slice pertama di [tasks.md](tasks.md#task-01--vertical-slice-pertama-create-dan-list-kontrak).
