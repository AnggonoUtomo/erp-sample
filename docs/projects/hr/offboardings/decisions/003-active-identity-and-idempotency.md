# ADR-003: Active Identity dan Idempotency Draft Offboarding

## Status

Accepted

## Date

2026-07-16

## Context

Form draft dapat terkirim berulang akibat double-click, retry browser, timeout, atau dua request yang berjalan hampir bersamaan. Optional contract dan exit date tidak cukup stabil sebagai identity guard: contract boleh tidak dipilih, sedangkan exit date dapat berubah tanpa menciptakan employment period baru.

Tanpa constraint database, pemeriksaan aplikasi saja masih memiliki race condition antara pembacaan existing case dan insert.

## Decision

- Satu offboarding non-terminal menggunakan `active_identity_key = employee:{employee_id}`.
- Payload draft ternormalisasi disimpan sebagai fingerprint SHA-256, bukan sebagai salinan data sensitif tambahan.
- Retry dengan identity dan fingerprint identik mengembalikan aggregate existing tanpa task atau audit baru.
- Request dengan identity sama tetapi fingerprint berbeda ditolak pada field employee.
- Unique nullable constraint pada `active_identity_key` menjadi concurrency guard terakhir.
- Fingerprint mencakup employee, optional contract, template, target final status, owner, exit date/type/reason, dan notes.
- Identity/fingerprint tidak diekspos pada props frontend atau audit values.
- Lifecycle terminal di task berikutnya wajib melepas `active_identity_key`; fingerprint tetap menjadi evidence internal.

## Alternatives considered

### Identity berdasarkan contract

Ditolak sebagai guard utama karena contract bersifat opsional. Request tanpa contract dapat berjalan paralel dengan request yang memilih contract untuk employee yang sama.

### Identity berdasarkan employee dan exit date

Ditolak karena perubahan exit date dapat menghasilkan case aktif kedua untuk proses keluar yang sama.

### Application lock tanpa unique constraint

Ditolak karena tidak cukup melindungi competing insert dan berbeda perilaku antar database/worker.

### Client-provided idempotency key

Ditunda karena form internal ini dapat memperoleh idempotency deterministic dari payload bisnis tanpa menambah kontrak input publik.

## Consequences

- Employee hanya dapat memiliki satu proses offboarding aktif.
- Retry identik aman dan tidak menggandakan audit.
- Perubahan payload setelah draft terbentuk harus memakai lifecycle/edit contract khusus, bukan create ulang.
- Migration akan gagal secara fail-closed bila data existing sudah mengandung lebih dari satu offboarding aktif untuk employee yang sama; data tersebut harus direkonsiliasi sebelum deploy.
- Cancel/completion wajib mengosongkan active identity agar employment period berikutnya dapat membuat case baru.
