# ADR-001: Console sebagai operational foundation

## Status

Accepted

## Date

2026-07-19

## Context

Project `Console` sudah menjadi area administrasi utama aplikasi: login, dashboard, user management, access control, system settings, audit, notification, queue/scheduler monitor, dan backup/restore. Namun dokumentasi project Console belum dibuat setelusur project HR. Akibatnya beberapa keputusan dasar Console tersebar di blueprint, baseline review, tests, dan source code.

Kita membutuhkan dokumen yang menjelaskan Console dari nol agar:

- programmer memahami mengapa Console berbeda dari project bisnis;
- agent berikutnya tidak mencampur domain HR/Payroll/CRM ke Console;
- role protected `super-system` dan authorization boundary tidak terus diperdebatkan ulang;
- module Console bisa dievaluasi satu per satu dengan task kecil;
- backup/settings/audit/queue/scheduler punya runbook dan acceptance criteria yang jelas.

## Decision

Tetapkan `Console` sebagai operational foundation project.

Artinya:

- Console menjadi pusat identity, authorization, settings, observability, runtime operation, dan recovery.
- Console bukan domain bisnis dan tidak menyimpan lifecycle HR/Payroll/CRM.
- Route Console tetap tanpa prefix project agar menjadi admin area utama.
- Project bisnis boleh memakai Console User sebagai identity reference, tetapi data bisnis tetap berada di project masing-masing.
- Role `super-system` adalah role protected; hanya visible untuk akun super-system dan tidak bisa dikelola dari UI biasa.
- Role/permission Spatie tidak memakai soft delete karena merupakan konfigurasi authorization aktif.
- Audit/log/backup/settings memiliki security boundary sendiri dan wajib diuji.

## Alternatives considered

### Membiarkan Console tanpa project docs

- Pro: Tidak ada pekerjaan dokumentasi tambahan.
- Cons: Keputusan tersebar, sulit ditelusuri, dan agent/human mudah membuat perubahan yang melanggar boundary.
- Rejected: Project HR sudah memakai pola dokumentasi yang terbukti membantu eksekusi incremental.

### Memindahkan semua admin feature ke project bisnis masing-masing

- Pro: Setiap project punya admin sendiri.
- Cons: Identity, authorization, settings, audit, queue, scheduler, dan backup menjadi duplikatif dan sulit diamankan.
- Rejected: Console lebih tepat menjadi shared operational layer.

### Membuat Console sebagai domain bisnis

- Pro: Semua fitur bisa masuk satu tempat.
- Cons: Membuat boundary kabur; HR/Payroll/CRM bisa bercampur dengan operational admin.
- Rejected: Console harus tetap operational foundation, bukan domain bisnis.

### Menyembunyikan `super-system` dari semua UI tanpa pengecualian

- Pro: Mengurangi risiko role sensitif terlihat.
- Cons: Akun super-system sendiri kehilangan visibilitas administrasi lengkap dan terasa seperti role hilang.
- Rejected: Visibility harus scoped: hanya akun super-system yang melihat role ini, tetap protected/read-only untuk mutasi biasa.

## Consequences

- Console wajib punya docs project seperti HR.
- Setiap module Console dievaluasi dengan task kecil, bukan rewrite besar.
- Protected role logic harus dijaga oleh backend validation, policy, dan tests.
- Module bisnis harus memakai Console sebagai dependency eksplisit bila butuh user/permission/settings/audit.
- Jika future feature seperti SSO, scheduled backup, retention audit, atau approval role dibutuhkan, buat ADR baru sebelum coding.

## Follow-up

1. Buat `docs/projects/console/module-guide.md`.
2. Buat `docs/projects/console/roadmap.md`.
3. Telusuri `AccessControls` dan `UserManagements` lebih dulu.
4. Catat gap koreksi per module di `tasks.md`.

