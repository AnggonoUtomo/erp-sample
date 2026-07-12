# 05 — Planning and Task Breakdown

Status: seluruh TASK-01–10 selesai. Evidence checkpoint dirangkum di [guide plan koreksi](07-guide-plan-koreksi.md#status-eksekusi).

## TASK-01 — Isolasi filesystem test

- Tujuan: membuat registry/generator tests deterministic dan parallel-safe.
- File: `ModuleRegistry.php`, `MakeModuleCommand.php`, kedua test terkait, maksimal satu config/support seam.
- Acceptance: AC-01/AC-02; tidak ada write ke shared `TmpProject`.
- Test: focused tests, full serial ×3, parallel ×3, clean `git status`.

## TASK-02 — Stabilkan test avatar

- Tujuan: membuktikan add/remove avatar tanpa error fixture.
- File: `UserManagementTest.php`, test setup/support media bila perlu.
- Acceptance: media DB dan disk benar sebelum/sesudah remove; focused/full suite hijau.
- Test: focused test diulang, full suite, disk cleanup assertion.

## TASK-03 — Hilangkan drift acronym

- Tujuan: hanya satu canonical `hr/hr-reference-data`.
- File: page stray, generator normalization/test, referensi resolver bila ada.
- Acceptance: tidak ada referensi `h-r-reference-data`; generator HR regression lulus.
- Test: `rg`, generator test, typecheck, build.

## TASK-04 — Quality gates non-mutating

- Tujuan: memisahkan check dan fix.
- File: `package.json`, ESLint config bila perlu, Composer scripts, CI pada task terpisah.
- Acceptance: seluruh check tidak mengubah tree dan exit 0.
- Test: status sebelum/sesudah, Pint/ESLint/Prettier/typecheck/build.

## TASK-05 — Validator contract modul

- Tujuan: mendeteksi drift manifest/folder/dependency/export.
- File: command baru, registry/schema support, tests, docs command.
- Acceptance: AC-03, output manusia + JSON stabil.
- Test: fixture valid/invalid/cycle/duplicate/missing export.

## TASK-06 — Authorization dan negative coverage matrix

- Tujuan: membuktikan semua mutation menolak guest/user tanpa permission dan input boundary.
- File: test per modul; production code hanya jika test menemukan defect dan dibuat task koreksi terpisah.
- Acceptance: matrix route×permission terdokumentasi dan hijau.
- Test: feature test per module + full suite.

## TASK-07 — Hardening full restore

- Tujuan: membuat operasi destructive dapat divalidasi, diaudit, dan dipulihkan.
- File: backup request/service/controller/policy, audit/event, focused tests, ADR/runbook.
- Acceptance: AC-05/AC-06; threat model disetujui sebelum coding.
- Test: traversal, symlink, bomb/limit, checksum/version, invalid SQL, partial failure, rollback.

## TASK-08 — Dekomposisi service berisiko

- Tujuan: memisahkan orchestration, validation, persistence, adapter tanpa behavior change.
- File: satu service per increment + characterization tests; maksimum lima file per task turunan.
- Acceptance: public behavior sama, complexity/tanggung jawab berkurang, semua gate hijau.
- Test: characterization + full suite.

## Checkpoints

- CP-1 setelah TASK-01–02: baseline tests stabil.
- CP-2 setelah TASK-03–05: contract dan gates enforceable.
- CP-3 setelah TASK-06–07: security boundary terbukti.
- CP-4 setelah TASK-08: maintainability membaik tanpa behavior drift.

Plan berstatus **Implemented sampai CP-4** pada 2026-07-12. Pekerjaan dependency audit tetap merupakan scope terpisah.
