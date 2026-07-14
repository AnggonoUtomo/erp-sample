# Project HR — Onboardings

Paket ini menjadi acuan implementasi module `Onboardings`: HR membuat onboarding dari template checklist, menugaskan task, memantau progres, dan menyelesaikan proses masuk employee secara terkontrol. Dokumen ini adalah spesifikasi dan rencana; belum menandakan module sudah diimplementasikan.

## Urutan baca

1. [README](README.md) — tujuan, batas domain, dan peta dokumen.
2. [Specification](specification.md) — requirement, non-scope, state, struktur, command, acceptance criteria, dan test plan.
3. [ADR-001](decisions/001-checklist-driven-onboarding.md) — alasan template disalin menjadi snapshot task per onboarding.
4. [Implementation plan](implementation-plan.md) — fase, dependency graph, risiko, dan checkpoint.
5. [Tasks](tasks.md) — task kecil yang dapat dieksekusi satu per satu.

Dokumen konteks terkait:

- [HR Roadmap](../roadmap.md) — posisi Onboardings dalam lifecycle HR.
- [Employees](../employees/README.md) — master employee yang menjadi owner onboarding.
- [Employee Contracts](../employee-contracts/README.md) — kontrak/employment period yang menjadi konteks onboarding.
- [Employee Movements](../employee-movements/README.md) — perubahan assignment setelah employee aktif, bukan bagian onboarding MVP.
- [HR Module Guide](../module-guide.md) — aturan folder, permission, backend, dan frontend.

## Masalah yang diselesaikan

Tanpa proses formal, aktivitas seperti pengumpulan data, orientasi, provisioning akses, dan konfirmasi kesiapan kerja tersebar di chat atau spreadsheet. HR tidak dapat memastikan apa yang belum selesai, siapa penanggung jawabnya, atau checklist mana yang berlaku saat employee masuk.

## Arah yang dipilih

MVP memakai onboarding berbasis checklist:

- template mendefinisikan task standar;
- saat onboarding dimulai, task template disalin menjadi snapshot;
- setiap onboarding terkait satu employee dan satu employment period/contract bila tersedia;
- HR dapat assign, complete, reopen, cancel, dan menyelesaikan onboarding melalui transition eksplisit;
- histori tidak berubah ketika template diedit kemudian.

## Pengguna

- `hr-manager`: mengelola template, memulai, membatalkan, dan menyelesaikan onboarding;
- `hr-officer`: memulai onboarding dan mengelola task sesuai permission;
- assignee internal: melihat atau menyelesaikan task yang diberikan pada fase berikutnya;
- `hr-viewer`: membaca progres tanpa melakukan mutation.

## Output module

- template checklist onboarding yang reusable;
- onboarding case per employee/employment period;
- snapshot task beserta urutan, assignee, due date, dan completion evidence ringkas;
- progress summary yang deterministik;
- audit trail transition penting;
- event/integration contract minimal setelah schema disetujui.

## Status

`PROPOSED` — specification dan ADR memerlukan persetujuan sebelum Task 01 dijalankan.
