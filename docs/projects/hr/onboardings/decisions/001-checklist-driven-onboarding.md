# ADR-001: Checklist Template Disalin Menjadi Snapshot Task Onboarding

## Status

Accepted

## Date

2026-07-15

## Context

HR membutuhkan checklist onboarding yang dapat digunakan ulang. Template akan berubah dari waktu ke waktu: task baru ditambahkan, urutan diubah, atau wording diperbaiki. Jika onboarding existing selalu membaca item template terbaru, histori proses employee lama ikut berubah dan evidence completion menjadi tidak dapat dipercaya.

Kebutuhan utama:

- HR dapat memelihara template tanpa merusak case berjalan/selesai;
- setiap onboarding memiliki ordered task yang stabil;
- task dapat memiliki assignee, due date, state, actor, dan completion note sendiri;
- create onboarding harus atomic;
- scope tidak berkembang menjadi workflow engine generik.

## Decision

Saat onboarding dibuat, semua item dari template aktif disalin menjadi `OnboardingTask` milik onboarding tersebut. Task menyimpan snapshot minimal:

- source template item identifier untuk traceability;
- title dan description;
- category;
- required flag;
- sort order;
- calculated due date/default offset context;
- default assignee rule yang telah di-resolve atau dicatat aman.

Perubahan template setelah itu hanya berlaku untuk onboarding baru. Task existing berubah hanya melalui lifecycle task yang eksplisit. Onboarding menjadi aggregate root yang menghitung progress dan menjaga completion invariant.

## Alternatives considered

### Membaca template secara live

- Kelebihan: sedikit row dan update template langsung terlihat.
- Kekurangan: histori berubah, task completed dapat hilang/berganti arti, audit tidak reproducible.
- Ditolak karena merusak integritas evidence.

### Menyimpan seluruh checklist sebagai JSON pada onboarding

- Kelebihan: snapshot mudah dibuat dalam satu kolom.
- Kekurangan: assignment, filtering overdue, constraint state, indexing, dan audit per task menjadi sulit.
- Ditolak karena task adalah data operasional relational.

### Workflow engine generik

- Kelebihan: dapat dipakai approval dan modul lain.
- Kekurangan: state designer, expression rules, versioning, dan runtime engine jauh melampaui MVP.
- Ditolak sebagai abstraksi prematur; desain ulang hanya jika minimal tiga use case nyata membutuhkan semantics sama.

### Checklist hardcoded

- Kelebihan: implementasi paling sederhana.
- Kekurangan: setiap perubahan bisnis memerlukan deployment dan tidak mendukung variasi template.
- Ditolak karena HR perlu mengelola checklist operasional.

## Consequences

### Positive

- Histori onboarding stabil dan dapat diaudit.
- Progress/reports dapat dihitung dari relational task.
- Template dapat berevolusi tanpa migration histori.
- Assignment dan due date dapat dikelola per employee.

### Negative

- Data title/description terduplikasi sebagai snapshot.
- Create onboarding memerlukan transaction dan rollback menyeluruh.
- Koreksi typo pada template tidak otomatis memperbarui case existing.
- Traceability memerlukan source identifier dan audit yang jelas.

## Implementation constraints

- Snapshot dibuat di transaction yang sama dengan onboarding.
- Kegagalan satu item membatalkan onboarding dan seluruh task.
- Source template/item boleh diarsipkan tetapi tidak boleh menghilangkan history.
- Tidak ada foreign key behavior yang menghapus task snapshot akibat template deletion.
- Completion selalu membaca task snapshot, bukan current template.
- Binary evidence tetap melalui Employee Documents/DMS.

## Acceptance record

Keputusan snapshot checklist dan pelaksanaan Task 01 disetujui pada 2026-07-15. Keputusan berikut tetap menjadi gate untuk task runtime terkait dan tidak menghalangi foundation contract:

- employment period identity harus diputuskan sebelum Task 04;
- required task skip policy dan assignee type harus diputuskan sebelum Task 08–09.

## Related documents

- [Specification](../specification.md)
- [Implementation plan](../implementation-plan.md)
- [Tasks](../tasks.md)
- [HR Roadmap](../../roadmap.md)
