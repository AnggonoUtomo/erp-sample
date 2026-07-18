# ADR-001: Stable HR integration contracts

## Status

Accepted

## Date

2026-07-18

## Context

Project HR sekarang sudah memiliki banyak source module: Employees, Employee Contracts, Employee Documents, Employee Movements, Onboardings, Offboardings, dan HR Reports. Modul berikutnya seperti Attendance dan Payroll akan membutuhkan data HR yang sama, tetapi jika consumer membaca langsung tabel/model internal, perubahan kecil di HR dapat memecahkan banyak modul.

Kita membutuhkan boundary yang:

- stabil untuk consumer;
- aman dari kebocoran PII;
- mudah dites;
- tidak membuat lifecycle baru;
- tidak memaksa queue/outbox/API publik terlalu dini.

## Decision

Buat `HR Integration Contracts` sebagai kontrak internal versioned.

MVP hanya menyediakan DTO/snapshot/event schema dan command inspeksi non-mutating. Consumer downstream tidak boleh membaca tabel internal HR secara bebas; consumer harus memakai provider/contract resmi.

Contract v1 memakai prinsip:

- additive field only;
- explicit version suffix, contoh `EmployeeSnapshotV1`;
- explicit date semantics, contoh `asOf` atau `effectiveDate`;
- default payload minim PII;
- forbidden-field guard untuk field sensitif;
- tidak ada listener downstream spekulatif pada MVP.

## Alternatives considered

### Direct query ke tabel/model HR

Pros:

- Cepat untuk dibuat.
- Tidak perlu layer baru.

Cons:

- Consumer rapuh saat schema HR berubah.
- Sulit mencegah kebocoran PII.
- Logic active/terminal/effective date akan diduplikasi.

Rejected karena berisiko tinggi untuk Attendance dan Payroll.

### Public REST API internal sejak awal

Pros:

- Boundary jelas.
- Bisa dipakai aplikasi lain di masa depan.

Cons:

- Overkill untuk monolith MVP.
- Membutuhkan auth/token/rate limit/versioning publik lebih cepat.
- Menambah surface security sebelum diperlukan.

Rejected untuk MVP, bisa dievaluasi lagi saat ada integrasi lintas server.

### Queue/outbox event bus sejak awal

Pros:

- Lebih siap untuk event-driven architecture.
- Delivery/retry bisa distandarkan.

Cons:

- Kompleksitas tinggi.
- Belum ada consumer Attendance/Payroll yang siap.
- Risiko membuat listener mutation spekulatif.

Rejected untuk MVP. Event schema boleh dibuat, delivery production ditunda.

## Consequences

- Attendance/Payroll nanti mulai dari contract yang stabil.
- Perubahan internal HR bisa lebih aman selama contract v1 tetap kompatibel.
- Developer harus menambah field lewat DTO/schema, bukan langsung mengambil kolom bebas.
- Jika contract v1 kurang, buat `V2`; jangan mengubah semantics v1 secara breaking.
- Ada sedikit layer tambahan, tetapi risiko integrasi jangka panjang turun signifikan.

## Follow-up

- Review dan approve specification sebelum coding.
- Implement registry dan privacy guard sebagai vertical slice pertama.
- Dokumentasikan consumer handoff untuk Attendance dan Payroll setelah event/snapshot v1 stabil.
