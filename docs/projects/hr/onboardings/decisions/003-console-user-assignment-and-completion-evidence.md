# ADR-003: Console User assignment dan completion evidence

## Status

Accepted — 2026-07-15

## Context

Task onboarding membutuhkan penanggung jawab dan bukti penyelesaian yang dapat diaudit. MVP belum memiliki team/role virtual sebagai actor runtime, sementara Console Users sudah menjadi identity boundary project.

## Decision

- Assignee MVP adalah satu Console User non-deleted atau kosong.
- Assignment diperbolehkan saat onboarding `DRAFT` atau `IN_PROGRESS`.
- Assignment task terminal ditolak; perubahan berikutnya harus melalui controlled reopen.
- Eksekusi task hanya mengikuti `PENDING -> IN_PROGRESS -> COMPLETED` ketika onboarding `IN_PROGRESS`.
- Completion menyimpan `completed_by_user_id`, `completed_at`, dan note opsional maksimum 2.000 karakter.
- User yang kemudian di-soft-delete tetap dapat ditampilkan pada histori melalui relation `withTrashed`; hard delete membuat foreign key menjadi `null` tanpa menghapus evidence waktu/note.
- Audit menyimpan identifier actor/status, tetapi tidak menduplikasi completion note untuk mengurangi paparan data.

## Consequences

- Authorization tetap menggunakan `onboardings.task-update` atau `onboardings.manage`.
- Assignment role/team virtual, delegation, dan notification tetap deferred.
- Task mutation mengunci onboarding lebih dahulu lalu task agar urutan lock konsisten.
- Frontend visibility hanya UX; seluruh route tetap diperiksa policy dan state server-side.

## Alternatives considered

- **Role virtual sebagai assignee:** ditunda karena belum ada owner runtime dan aturan penyelesaian yang disetujui.
- **Assignee wajib:** ditolak untuk MVP karena draft checklist boleh disiapkan sebelum pembagian kerja selesai.
- **Completion langsung dari PENDING:** ditolak agar histori membedakan task yang belum dimulai dari pekerjaan aktif.
