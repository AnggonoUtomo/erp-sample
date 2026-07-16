# Project HR — Offboardings

Paket ini menjadi acuan pengembangan module `Offboardings`: HR merencanakan keluarnya employee, menjalankan checklist handover, memastikan pekerjaan wajib selesai, lalu menerapkan pemutusan employment secara effective-dated dan dapat diaudit.

## Urutan baca

1. [README](README.md) — tujuan, pengguna, batas domain, dan peta dokumen.
2. [Idea refinement](00-idea-refinement.md) — opsi, risiko, asumsi, dan arah MVP yang dipilih.
3. [Specification](specification.md) — requirement, state contract, non-scope, struktur, command, acceptance criteria, dan test plan.
4. [ADR-001](decisions/001-checklist-snapshot-and-exit-readiness.md) — snapshot checklist dan pemisahan readiness dari final employment exit.
5. [ADR-002](decisions/002-effective-dated-termination-boundary.md) — ownership perubahan Employee/Contract dan batas integrasi downstream.
6. [Implementation plan](implementation-plan.md) — dependency graph, fase, risiko, dan rollback.
7. [Tasks](tasks.md) — task kecil berurutan dengan file, acceptance criteria, dan cara test.
8. [Checkpoint A](01-template-checkpoint-a.md) — evidence template contract dan gate snapshot untuk Task 04.

Dokumen terkait:

- [HR Roadmap](../roadmap.md)
- [Employees](../employees/README.md)
- [Employee Contracts](../employee-contracts/README.md)
- [Employee Movements](../employee-movements/README.md)
- [Employee Documents](../employee-documents/README.md)
- [Onboardings](../onboardings/README.md)
- [HR Module Guide](../module-guide.md)

## Masalah yang diselesaikan

Proses resign, termination, end-of-contract, retirement, dan separation lain sering tersebar di spreadsheet atau chat. Akibatnya HR sulit memastikan:

- exit date dan reason sudah disetujui;
- handover, akses, dokumen, dan pengembalian aset sudah ditangani;
- siapa yang bertanggung jawab atas setiap task;
- kapan Employee dan Contract boleh diakhiri;
- apakah perubahan employment dilakukan sekali, atomik, dan dapat diaudit.

## Arah MVP

- template checklist disalin menjadi task snapshot saat draft dibuat;
- satu employment period hanya boleh memiliki satu offboarding aktif;
- checklist dapat disiapkan sebelum exit date;
- `READY_FOR_EXIT` berarti seluruh task wajib sudah terminal, tetapi employment belum otomatis berakhir;
- `COMPLETED` hanya tercapai melalui finalisasi effective-dated yang memperbarui Employee dan Contract lewat kontrak module resmi;
- file evidence tetap memakai Employee Documents/DMS;
- Attendance dan Payroll tidak diubah langsung dan event lintas project tidak dipublikasikan sebelum consumer menyetujui contract.

## Pengguna

- `hr-manager`: mengelola template, mengaktifkan, menyatakan ready, membatalkan, dan memfinalisasi exit;
- `hr-officer`: membuat draft dan mengelola task sesuai permission;
- assignee internal: menjalankan task yang diberikan;
- `hr-viewer`: membaca proses dan histori tanpa mutation.

## Output module

- template checklist offboarding reusable;
- offboarding case per employee/employment period;
- snapshot exit reason, exit date, owner, contract, dan task;
- progress serta overdue summary deterministic;
- completion evidence dan audit trail;
- final employment exit yang diterapkan tepat satu kali melalui boundary resmi.

## Status

`TASK 04 COMPLETE` — Task 01–04 selesai pada 2026-07-16. Authorized HR dapat mengelola template serta membuat draft offboarding dengan ordered task snapshot atomic. Employee, contract, target final status, owner, exit date/type/reason, dan template divalidasi tanpa mengubah employment profile. Duplicate/idempotency guard tetap menjadi scope Task 05; integration event downstream tetap deferred.
