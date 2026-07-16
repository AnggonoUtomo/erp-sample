# Idea Refinement — HR Offboardings

## Problem statement

Bagaimana HR dapat mengelola keluarnya employee secara tertib dari rencana sampai effective exit, tanpa checklist yang berubah diam-diam, tanpa mutation employment prematur, dan tanpa membuat workflow engine atau integrasi spekulatif?

## Target user dan success

Pengguna utama adalah HR Manager dan HR Officer. MVP berhasil ketika satu offboarding dapat dibuat dari template, dikerjakan per task, dinyatakan siap, dan difinalisasi pada exit date dengan Employee/Contract konsisten serta audit lengkap.

## Opsi yang dievaluasi

### 1. Checklist-only

HR menutup checklist, sedangkan Employee dan Contract diubah manual pada menu lain.

- Nilai: cepat dan sederhana.
- Risiko: dua langkah manual mudah terlewat; checklist selesai tidak menjamin employment benar-benar berakhir.
- Keputusan: tidak cukup sebagai hasil final, tetapi cocok sebagai slice awal.

### 2. Checklist + atomic effective exit

Offboarding memisahkan readiness dari finalisasi. Setelah task wajib selesai, HR memfinalisasi pada/ setelah exit date; module mengoordinasikan perubahan melalui contract resmi Employees dan Employee Contracts.

- Nilai: satu alur operasional dan sumber audit yang jelas.
- Risiko: memerlukan boundary lintas module dan concurrency guard.
- Keputusan: arah MVP yang dipilih, dibangun bertahap.

### 3. Workflow engine generik

Template mendukung approval graph, condition, parallel gateway, SLA, reminder, dan integrasi bebas.

- Nilai: fleksibel.
- Risiko: jauh melampaui kebutuhan nyata dan sulit diaudit.
- Keputusan: ditolak untuk MVP.

### 4. Event-first asynchronous exit

Offboarding hanya mengirim event, lalu Employee/Contract/Attendance/Payroll bereaksi sendiri.

- Nilai: coupling rendah.
- Risiko: consumer belum disetujui, partial failure lebih sulit dijelaskan, dan eventual consistency tidak diperlukan untuk mutation HR inti.
- Keputusan: event downstream deferred; finalisasi HR inti tetap synchronous dan atomic.

## Recommended direction

Gunakan checklist snapshot dengan state eksplisit:

```txt
DRAFT -> IN_PROGRESS -> READY_FOR_EXIT -> COMPLETED
   \          \               \
    ----------------------------------> CANCELLED
```

`READY_FOR_EXIT` adalah checkpoint bisnis, bukan status employment. `COMPLETED` hanya dicapai setelah effective exit berhasil diterapkan tepat satu kali.

## Key assumptions

- Employee dan employment period sudah tersedia sebelum draft dibuat.
- Exit date dan reason dapat ditetapkan sebelum seluruh checklist selesai.
- HR membutuhkan perbedaan jelas antara “task selesai” dan “employment sudah berakhir”.
- Employment status final dapat dipilih berdasarkan master yang memiliki `is_final_status=true`.
- Consumer Attendance/Payroll belum siap menerima public event.

## MVP scope

- template dan ordered checklist;
- draft, task snapshot, assignment, complete/skip/reopen;
- exit date, exit type/reason, owner, dan optional active contract;
- ready, cancel, finalization, archive/restore, filters, dan due command;
- atomic Employee/Contract termination melalui interface resmi;
- authorization dan audit untuk seluruh mutation.

## Not doing

- asset inventory engine — hanya placeholder/reference;
- revocation akun otomatis — memerlukan kebijakan Console/security terpisah;
- payroll final settlement — domain Payroll;
- attendance cutoff implementation — menunggu consumer contract;
- email/WhatsApp reminder — bukan core invariant;
- e-signature dan exit interview survey engine;
- file storage baru;
- workflow designer generik;
- reopen offboarding yang sudah completed/cancelled.

## Risks to validate early

- status final master ambigu atau lebih dari satu;
- contract sudah ended/cancelled sebelum finalization;
- exit date berubah ketika case sudah ready;
- dua request finalize berjalan bersamaan;
- checklist completion menghasilkan rasa aman palsu padahal Employee/Contract belum berubah.
