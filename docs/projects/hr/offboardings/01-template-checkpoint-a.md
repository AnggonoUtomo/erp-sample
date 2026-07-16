# Checkpoint A — Offboarding Template Contract

**Status:** PASS  
**Tanggal:** 2026-07-16  
**Verified baseline:** `52fbba7`

## Scope yang diverifikasi

Checkpoint ini menutup Task 01–03:

- module, permission, state, route, dan navigation contract;
- create/list template beserta ordered items;
- signed due offset terhadap exit date;
- archive/restore tanpa hard delete;
- authorization, transaction, audit, pagination, dan UI dasar.

Checkpoint ini tidak mencakup draft Offboarding, task snapshot runtime, Employee/Contract mutation, atau integration event.

## Evidence quality gate

| Gate                                                                                   | Hasil                                                                 |
| -------------------------------------------------------------------------------------- | --------------------------------------------------------------------- |
| Migration isolated `up()`                                                              | PASS — kedua tabel terbentuk                                          |
| Migration isolated `down()`                                                            | PASS — kedua tabel terhapus kembali                                   |
| `php artisan migrate --pretend --path=app/Modules/HR/Offboardings/Database/Migrations` | PASS — SQL, foreign key restrict, unique code, dan unique order valid |
| `php artisan module:validate`                                                          | PASS                                                                  |
| `php artisan test --filter='Offboarding(Foundation\|Template)'`                        | PASS — 13 tests, 130 assertions                                       |
| `vendor/bin/pint --test ...`                                                           | PASS                                                                  |
| `npm run typecheck`                                                                    | PASS                                                                  |
| `npm run build`                                                                        | PASS — 2.183 modules transformed                                      |
| Full backend regression pada Task 03                                                   | PASS — 384 tests, 1.996 assertions                                    |
| Frontend regression pada Task 03                                                       | PASS — 6 files, 11 tests                                              |

## Correctness review

- Template dan ordered items dibuat dalam satu transaction.
- Code dinormalisasi uppercase dan unik termasuk selama soft-deleted.
- Item order disimpan melalui `sort_order` dengan unique constraint per template.
- Signed due offset dibatasi `-365..365`.
- Failure pada audit me-rollback template dan seluruh items.
- Archive mempertahankan items; restore mengembalikan identity yang sama.
- Query default mengecualikan archived dan filter eksplisit menampilkan history.
- List dipaginate 20 item dan eager-load ordered items.

## Authorization dan security review

- Seluruh route memakai middleware `auth`.
- Create/list/archive/restore dilindungi policy middleware.
- `hr-viewer` hanya dapat membaca; lifecycle template hanya untuk `template-manage` atau `manage`.
- Tidak ada force-delete route.
- Audit hanya membawa code, name, active flag, item count, dan archive timestamp; isi checklist panjang tidak disalin ke audit.
- Tidak ada PII, secret, binary, direct storage reference, atau dependency Attendance/Payroll.
- Visibility tombol frontend bukan authorization boundary.

## Architecture review

- Write flow mengikuti Controller → FormRequest → DTO → Service → Transaction → Model.
- Template dan item tetap dimiliki module Offboardings.
- Foreign key item memakai `restrictOnDelete`.
- Module manifest belum mempublikasikan event/listener spekulatif.
- Navigation hanya membuka slice template yang sudah policy-backed.
- Archive memakai soft delete sehingga template dapat menjadi traceable source bagi snapshot pada Task 04.

## Review template sebagai source

Template hanya boleh menjadi **source saat create draft**. Setelah draft terbentuk, Offboarding tidak boleh membaca current template items untuk progress atau histori.

Task 04 wajib menyalin field berikut ke task snapshot:

- source template item identifier;
- title dan description;
- category;
- required flag;
- sort order;
- signed due offset;
- calculated due date terhadap exit date;
- default assignee context;
- initial task status.

Test Task 04 wajib membuktikan:

1. edit atau archive template setelah draft dibuat tidak mengubah task existing;
2. kegagalan pada salah satu task me-rollback seluruh aggregate;
3. progress dan detail membaca task snapshot, bukan current template;
4. source template/item tidak dapat menghapus histori snapshot;
5. hanya template aktif dan tidak archived yang dapat dipilih untuk draft baru.

## Findings

Tidak ada blocker untuk Task 04.

Residual constraint: tabel Offboarding case belum tersedia, sehingga used-template protection saat ini berasal dari soft delete, preserved items, reserved code, dan ketiadaan force delete. Task 04 harus menambahkan reference yang tidak memiliki cascade delete terhadap snapshot history.

## Gate decision

Checkpoint A **PASS**. Task 04 — Draft dan task snapshot boleh dimulai dengan syarat:

- semantics employment identity, exit date, exit type/reason, target final status, optional contract, dan owner mengikuti specification;
- create aggregate menggunakan transaction;
- template hanya dibaca sekali sebagai source snapshot;
- tidak ada perubahan Employee atau Employee Contract pada slice draft.
