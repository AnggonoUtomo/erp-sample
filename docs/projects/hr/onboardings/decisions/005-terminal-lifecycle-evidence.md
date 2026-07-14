# ADR-005 — Terminal lifecycle dan evidence

**Status:** Accepted
**Tanggal:** 2026-07-15

## Konteks

Onboarding perlu ditutup secara eksplisit tanpa generic update. Completion harus membuktikan seluruh task wajib sudah terminal, sedangkan pembatalan harus menjelaskan alasan dan tetap mempertahankan histori.

## Keputusan

- `IN_PROGRESS -> COMPLETED` hanya diizinkan ketika seluruh task wajib berstatus `COMPLETED` atau `SKIPPED` yang sah.
- Task opsional boleh belum terminal dan tidak di-skip otomatis saat onboarding selesai.
- `DRAFT` atau `IN_PROGRESS` dapat menjadi `CANCELLED` dengan alasan wajib maksimal 2.000 karakter.
- Completion dan cancellation menyimpan actor serta timestamp; cancellation juga menyimpan reason.
- Lifecycle service mengunci onboarding lalu seluruh task sebelum memeriksa completion invariant.
- Status terminal tidak dapat dibuka kembali pada MVP. Seluruh mutation task ditolak setelah onboarding terminal.
- Permission `onboardings.complete` dan `onboardings.cancel` menjadi approval operasional; MVP tidak menambah approval manager dua tahap.
- Terminal transition melepaskan `active_identity_key` agar employment identity yang sama dapat memiliki onboarding pengganti tanpa menghapus histori.

## Konsekuensi

- Race antara completion dan task mutation diserialisasi dengan urutan lock aggregate yang konsisten.
- Kesalahan operasional setelah terminal perlu onboarding baru; tidak ada reopen terminal terselubung.
- Evidence terminal dapat dibaca pada detail tanpa mengekspos data sensitif tambahan.
