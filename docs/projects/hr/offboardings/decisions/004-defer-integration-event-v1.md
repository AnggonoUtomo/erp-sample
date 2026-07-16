# ADR-004 — Defer integration event v1

**Status:** Accepted — Deferred  
**Tanggal:** 2026-07-17

## Konteks

Offboardings sudah memiliki finalisasi effective-dated yang mengubah Offboarding, Employee, dan optional Employee Contract secara atomic melalui gateway resmi pemilik domain. Setelah itu, domain lain seperti Attendance, Payroll, Accounting, Document Management, atau Console access control kemungkinan membutuhkan sinyal perubahan.

Namun saat evaluasi Task 16, belum ada consumer runtime dan belum ada persetujuan schema. Mempublikasikan event sekarang berarti producer menebak kebutuhan downstream. Itu berisiko membekukan public API yang salah, terlalu luas, membawa PII, atau sulit dimigrasikan.

## Keputusan

- Integration event v1 tetap deferred.
- Manifest Offboardings mempertahankan `events: []` dan `listeners: []`.
- Offboardings tidak menambah dependency langsung maupun optional dependency ke Attendance, Payroll, Accounting, Document Management, atau consumer downstream lain.
- Tidak dibuat DTO, schema, event class, dispatcher, listener, outbox, adapter, atau projector spekulatif.
- Candidate event pada roadmap hanya bahan diskusi, bukan kontrak normatif.

## Syarat membuka kembali gate

Gate hanya boleh dibuka jika seluruh bukti berikut tersedia:

1. Consumer module dan owner teknis telah ditentukan.
2. Consumer menjelaskan use case: keputusan apa yang dibuat setelah menerima event.
3. Field minimum, versi schema, compatibility, idempotency, ordering, retry, dan failure semantics disetujui producer dan consumer.
4. PII classification, audit/logging boundary, dan authorization handoff disetujui.
5. Contract test producer-consumer serta strategi rollout/rollback disiapkan.

## Kandidat payload untuk diskusi, bukan kontrak

Jika Payroll atau Attendance kelak membutuhkan sinyal exit, diskusi sebaiknya dimulai dari data minimal:

- schema version;
- offboarding identifier;
- employee identifier;
- effective exit date;
- final status identifier;
- occurred-at timestamp;
- idempotency/event id.

Nama event, field final, delivery channel, dan ordering belum disetujui dan tidak boleh diimplementasikan berdasarkan ADR ini saja.

## Konsekuensi

- MVP Offboardings tetap mandiri dan tidak memiliki side effect downstream.
- Domain downstream dapat merancang kebutuhan berdasarkan use case nyata sebelum schema dibekukan.
- Perubahan status gate memerlukan ADR pengganti dan approval eksplisit consumer.

