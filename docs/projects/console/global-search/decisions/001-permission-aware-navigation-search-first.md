# ADR-001: Permission-aware navigation search first

## Status

Accepted

## Date

2026-07-19

## Context

Console sudah memiliki search placeholder di header, tetapi belum aktif. Di sisi lain, sidebar/navigation sudah tersedia dari shared Inertia props dan sudah dipakai untuk menampilkan menu berdasarkan permission.

Ada beberapa kemungkinan bentuk global search:

- mencari menu/navigation;
- mencari entity seperti user, employee, contract, document, report;
- menjalankan command mutation seperti backup, restore, flush queue, atau archive data;
- full-text search dokumen.

Untuk MVP, risiko terbesar adalah disclosure data sensitif dan scope melebar terlalu cepat.

## Decision

Mulai global search dari **permission-aware navigation search**.

Artinya:

- search hanya membaca menu/sidebar/navigation;
- result hanya halaman yang user boleh akses;
- result bersifat navigasi read-only;
- tidak ada mutation command;
- tidak ada entity/database search pada MVP awal;
- entity search masa depan harus lewat provider contract yang terpisah dan di-approve.

## Alternatives considered

### Full entity search sejak awal

Pros:

- terasa lebih powerful;
- bisa langsung mencari user/employee/document/report.

Cons:

- membuka risiko PII dan data sensitif;
- butuh permission matrix lebih kompleks;
- butuh rate limit, provider contract, dan test lebih berat;
- mudah melebar menjadi search engine mini.

Rejected untuk MVP awal.

### Backend endpoint search generik sejak awal

Pros:

- satu pintu query untuk semua provider;
- lebih mudah dikembangkan ke entity search.

Cons:

- belum diperlukan untuk navigation-only;
- menambah route/security surface lebih awal;
- bisa mendorong implementasi entity search sebelum boundary matang.

Deferred sampai Task 05.

### Command palette dengan mutation action

Pros:

- admin power-user bisa melakukan aksi cepat.

Cons:

- risiko destructive action tinggi;
- perlu confirmation, audit, idempotency, dan authorization ganda;
- bertentangan dengan MVP read-only.

Rejected untuk MVP.

## Consequences

- MVP lebih kecil, aman, dan cepat diverifikasi.
- Search langsung berguna untuk user karena menu semakin banyak.
- Entity search tetap mungkin ditambahkan nanti lewat provider contract.
- Security boundary tetap sederhana: frontend result permission-aware, backend route tujuan tetap policy-gated.

## Follow-up

- Setelah navigation search stabil, evaluasi provider read-only untuk `Users` atau `Employees`.
- Jika entity search dibuka, buat ADR baru untuk provider contract, privacy guard, rate limit, dan audit policy.
