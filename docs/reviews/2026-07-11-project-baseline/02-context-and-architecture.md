# 02 — Context Engineering dan Arsitektur

## CTX-01 — Peta struktur

- `app/Modules/{Project}/{Module}`: pemilik use case bisnis, policy, request, service, transaction, dan contract discovery.
- `app/Shared`: konsep lintas project yang stabil dan bebas logika fitur.
- `app/Integration`: message/adapter/projector lintas boundary.
- `app/Support/Modules`: registry, provider discovery, permission registry, dan generator.
- `resources/js/pages/{project}/{module}`: Inertia pages per project/module.
- `resources/js/components/ui`: primitive UI bersama; `components`: shell/shared composites.
- `tests/Feature`: perilaku HTTP/use case; `tests/Unit`: shared/integration/registry.

## CTX-02 — Aturan modul

Canonical key adalah `Project.Module`; namespace mengikuti `App\Modules\{Project}\{Module}`; URL dan frontend slug kebab-case. Contract minimum yang diinginkan: `module.php`, `routes.php`, `permissions.php`, `navigation.php`, dan provider. Ketidakjelasan saat ini: guide menyebut file wajib, sedangkan runtime mempunyai `exports` dan ActivityCenters tanpa navigation. Rekomendasi: manifest/schema menjadi source of truth; validator menjelaskan required/optional berdasarkan exports.

Komunikasi lintas project harus melalui contract publik, domain event, integration adapter, atau Shared Kernel. Import model internal lintas project harus dianggap architectural violation kecuali didokumentasikan sementara.

## CTX-03 — Aturan frontend

- `index.tsx` adalah composer, bukan tempat seluruh state/UI/use-case.
- Type lokal di `types.ts`, opsi statis di `options.ts`, komponen besar di `{singular}-components`.
- Gunakan primitive bersama, Inertia/Ziggy, permission dari server, dan error handling yang konsisten.
- Acronym project dinormalisasi natural (`HR` → `hr`), bukan character split (`h-r`).
- Tambahkan rule aksesibilitas: label, focus management, keyboard path, status/error announcement, dan contrast.

## CTX-04 — Aturan backend

- Flow default: Route → middleware auth → Controller → FormRequest/Policy → DTO → Service → Transaction → Model → Event.
- Controller melakukan orchestration tipis; query harus scoped/paginated; mutation harus authorized dan atomic.
- Module tidak membaca internal module lain secara langsung tanpa contract.
- Operasi filesystem, backup, restore, mail, dan queue adalah boundary tidak tepercaya dan wajib mempunyai validation, audit, failure semantics, serta test negatif.

## CTX-05 — Aturan test

- Test tidak boleh menulis ke source tree global tanpa root yang dapat di-inject dan nama unik.
- Setiap test memiliki fixture dan cleanup miliknya sendiri; parallel-safe menjadi acceptance criterion.
- Test feature membuktikan happy path, authorization denial, validation boundary, rollback, dan side effect.
- Gate minimum: PHP test, Pint check, ESLint check non-mutating, Prettier check, TypeScript check, Vite build.

## Context pack yang disarankan per task

1. Baca `AGENTS.md` bila tersedia, lalu README folder audit ini.
2. Muat satu spec/task saja, maksimal file yang akan disentuh + test + satu contoh canonical.
3. Nyatakan konflik dokumentasi/kode sebelum memilih pola.
4. Catat bukti command dan diff scope setelah selesai.

## Keputusan dokumentasi tahap ini

Project membutuhkan satu contract machine-checkable untuk mengurangi drift antara guide, generator, dan runtime. Lihat [ADR-002](decisions/002-contract-as-source-of-truth.md).

