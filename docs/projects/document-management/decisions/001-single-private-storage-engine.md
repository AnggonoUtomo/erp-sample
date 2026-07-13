# ADR-001: Document Management sebagai single private storage engine

## Status

Accepted — 2026-07-13.

## Context

HR Employee Documents membutuhkan attach dan secure access, sementara Accounting, CRM, Legal, dan Operations kelak memiliki kebutuhan serupa. Menambahkan media collection/storage pada setiap domain menghasilkan banyak jalur upload/download, policy, checksum, retention, dan backup yang mudah berbeda.

Foundation juga harus mendukung driver storage berbeda tanpa menjadikan disk/path sebagai public contract. Filename dan MIME dari client tidak dapat dipercaya, dan public filesystem URL akan melewati authorization aplikasi.

## Decision

Project `DocumentManagement` menjadi owner tunggal logical document, immutable version, private binary storage, integrity metadata, access decision, delivery, dan lifecycle file lintas domain.

Domain consumer menyimpan opaque reference saja dan berkomunikasi melalui contract versioned. Semua binary I/O melewati `StorageAdapter`; object key dibuat server dan internal. Storage default private. DMS tidak menerbitkan raw path/object key dan consumer tidak membuat signed/public URL sendiri.

Ingestion memakai staged write, validation, checksum, idempotency, lalu atomic publish. Detach/archive consumer tidak berarti permanent delete. Retention, legal hold, dan permanent deletion membutuhkan keputusan terpisah.

## Alternatives considered

### Media collection per domain

- Cepat untuk fitur pertama, tetapi menduplikasi storage, policy, audit, scan, retention, dan delivery.
- Ditolak untuk regulated/business documents.

### Binary di database

- Transactional bersama metadata, tetapi memperbesar database/backup dan buruk untuk streaming/scale.
- Ditolak; database hanya menyimpan metadata dan opaque storage key internal.

### Public disk + generated URL

- Implementasi sederhana, tetapi URL melewati policy dan sulit revoke/audit.
- Ditolak; delivery harus melalui authorization DMS.

### DMS lengkap sekaligus

- Menghasilkan seluruh roadmap, tetapi menunda kebutuhan attach HR dan memperbesar risiko security.
- Ditolak untuk foundation; gunakan vertical slices sampai secure delivery terbukti.

## Consequences

### Positive

- Satu jalur security, integrity, backup, dan observability file.
- Consumer tidak terikat driver/path/schema DMS.
- Retry, versioning, dan lifecycle dapat diuji konsisten.

### Negative

- Attach adalah distributed workflow dan membutuhkan idempotency/failure cleanup.
- DMS menjadi critical dependency untuk ingestion/delivery, sehingga unavailable semantics wajib fail-closed.
- Malware scanning, retention, dan production storage memerlukan keputusan operasional sebelum launch.

## Approval gates

Keputusan single private storage engine telah diterima. Private-local single-server, upload limits/type allowlist, dan authorized-controller delivery ditetapkan oleh [ADR-002](002-upload-security-policy.md). [ADR-003](003-mvp-single-server-without-malware-scanner.md) mencatat risk acceptance tanpa scanner serta evaluasi wajib untuk scanner dan multi-server setelah MVP.
