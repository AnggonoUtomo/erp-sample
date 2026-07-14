# ADR-006 — Terminal history dan overdue business date

**Status:** Accepted
**Tanggal:** 2026-07-15

## Konteks

Operasional HR memerlukan filter overdue dan histori arsip tanpa membuat jalur mutation yang melewati cancellation evidence atau mengubah data berdasarkan waktu server secara tersembunyi.

## Keputusan

- Hanya onboarding `COMPLETED` atau `CANCELLED` yang dapat diarsipkan.
- Archive adalah soft delete dan tidak menyediakan force delete.
- Restore hanya menerima histori terminal terarsip dengan `active_identity_key` kosong; restore tidak mengaktifkan kembali onboarding.
- Filter list dipaginate dan mendukung employee, owner, template, status, start-date range, archived, serta overdue.
- Overdue berarti onboarding `DRAFT/IN_PROGRESS` memiliki task `PENDING/IN_PROGRESS` dengan `due_date` sebelum business date eksplisit.
- Command `hr:onboardings:overdue --date=YYYY-MM-DD` hanya membaca data, mengurutkan hasil berdasarkan ID, dan gagal dengan exit code non-zero bila tanggal hilang atau invalid.

## Konsekuensi

- Draft/in-progress harus dibatalkan dengan reason sebelum dapat menjadi histori arsip.
- Onboarding terminal dengan task opsional belum selesai tidak kembali dianggap overdue.
- Scheduler atau automation berikutnya wajib memberikan tanggal eksplisit dan tidak boleh mengandalkan timezone server secara implisit.
