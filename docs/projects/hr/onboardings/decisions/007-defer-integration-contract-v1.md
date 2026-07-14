# ADR-007 — Defer integration contract v1

**Status:** Accepted — Deferred
**Tanggal:** 2026-07-15

## Konteks

Task 13 hanya boleh mempublikasikan contract atau event setelah consumer nyata menyetujui kebutuhan interface. Dokumen Attendance saat ini masih berupa planning/roadmap; belum ada module consumer runtime, owner interface, use case pemicu, delivery guarantee, idempotency key, atau schema review yang disetujui.

Membuat `EmployeeOnboardingStarted` atau `EmployeeOnboardingCompleted` sekarang akan menjadikan tebakan producer sebagai public API dan berisiko membawa data yang salah, terlalu luas, atau tidak digunakan.

## Keputusan

- Integration contract v1 tetap deferred.
- Manifest Onboardings mempertahankan `events: []` dan `listeners: []`.
- Onboardings tidak menambah dependency langsung maupun optional dependency ke Attendance.
- Tidak dibuat DTO, schema, dispatcher, adapter, outbox, listener, atau event spekulatif.
- Candidate event pada roadmap bukan kontrak normatif dan belum boleh digunakan consumer.

## Syarat membuka kembali gate

Gate hanya dapat dibuka bila seluruh bukti berikut tersedia:

1. Consumer module dan owner teknis telah ditentukan.
2. Use case menjelaskan kapan data dibutuhkan dan keputusan apa yang dibuat consumer.
3. Field minimum, versioning, compatibility, idempotency, ordering, retry, dan failure semantics disetujui kedua domain.
4. PII classification dan authorization/logging boundary disetujui.
5. Contract test producer-consumer serta strategi rollout/rollback telah direncanakan.

## Kandidat payload untuk diskusi, bukan kontrak

Jika Attendance kelak hanya membutuhkan kesiapan employee, diskusi sebaiknya dimulai dari identifier stabil, contract version, onboarding status, effective/start date, dan occurred-at. Nama event maupun field tersebut belum disetujui dan tidak boleh diimplementasikan berdasarkan ADR ini.

## Konsekuensi

- MVP Onboardings tetap mandiri dan tidak memiliki side effect lintas project.
- Attendance dapat menyusun kebutuhan dari use case aktual sebelum producer membekukan schema.
- Perubahan status gate memerlukan ADR pengganti dan approval eksplisit consumer.
