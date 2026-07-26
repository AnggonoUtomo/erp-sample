# HR Foundation Review Plan

Dokumen ini menjadi panduan telusur ulang modul foundation HR satu per satu sebelum project dilanjutkan ke Attendance.

Tujuannya memastikan master data HR sudah matang, konsisten, aman, dan cukup menjadi sumber kebenaran untuk employee lifecycle serta consumer masa depan.

## Prinsip Review

- Review dilakukan per modul, tidak digabung besar.
- Setiap modul dicek dari sisi correctness, maintainability, security, test coverage, dan konsistensi arsitektur.
- Perubahan behavior harus kecil dan punya test.
- Jika hanya menemukan gap masa depan, catat sebagai follow-up tanpa memaksa implementasi.
- Jangan mulai Attendance sebelum gap foundation penting selesai atau sengaja didefer.

## Urutan Review

1. Departements
2. Positions
3. Job Levels
4. Work Locations
5. Employment Statuses
6. Employment Types
7. HR Reference Data
8. Organization Structures

Urutan ini dipilih karena employee assignment bergantung pada master data tersebut.

## Checklist Umum Per Modul

Untuk setiap modul, cek:

- Route, permission, navigation, provider/module manifest.
- Policy dan FormRequest authorization.
- Create/update/archive/restore/force-delete semantics.
- Validasi uniqueness dan dependency aktif.
- Soft delete behavior.
- Audit log untuk mutation penting.
- Seeder/default data jika diperlukan.
- Frontend list, filter, form, empty state, dan destructive dialog.
- Test authorization denial matrix.
- Test lifecycle utama.
- Tidak ada akses data sensitif yang tidak perlu.

## Modul 1 — Departements

Pertanyaan review:

- Apakah code/name unique dan stabil?
- Apakah departement yang dipakai position/employee tidak bisa force delete sembarangan?
- Apakah archive/restore menjaga histori assignment?
- Apakah naming `Departements` tetap diterima sebagai legacy module name atau perlu migration alias/documentation?

Acceptance review:

- CRUD dan archive behavior sesuai docs.
- Dependency guard jelas.
- Test route permission dan deletion guard tersedia.

## Modul 2 — Positions

Pertanyaan review:

- Apakah position selalu milik departement aktif?
- Apakah perubahan departement pada position aman terhadap employee assignment?
- Apakah position yang dipakai employee tidak bisa dihapus destruktif?
- Apakah title/code konsisten untuk reporting?

Acceptance review:

- Position tidak orphan.
- Relation ke Departements stabil.
- Test dependency dan permission tersedia.

## Modul 3 — Job Levels

Pertanyaan review:

- Apakah grade/level unik dan mudah dipakai lintas departement?
- Apakah job level yang dipakai employee terlindungi?
- Apakah sorting level deterministic?

Acceptance review:

- Job level bisa menjadi referensi stabil untuk report dan payroll future.
- Soft delete/restore aman.

## Modul 4 — Work Locations

Pertanyaan review:

- Apakah physical/remote location jelas?
- Apakah latitude, longitude, dan radius valid?
- Apakah Google Maps key tetap masked/encrypted via System Settings?
- Apakah location yang dipakai employee/geofence tidak force delete sembarangan?

Acceptance review:

- Data lokasi aman untuk Attendance nanti.
- Tidak ada secret map key bocor ke audit/props.

## Modul 5 — Employment Statuses

Pertanyaan review:

- Apakah flag `requires_attendance`, `included_in_payroll`, dan `is_terminal` jelas?
- Apakah status terminal seperti resigned/terminated tidak masuk Attendance/Payroll future?
- Apakah status yang dipakai employee terlindungi?

Acceptance review:

- Status bisa menjadi contract operasional untuk employee lifecycle.
- Denial matrix update/delete tersedia.

## Modul 6 — Employment Types

Pertanyaan review:

- Apakah flag contract end date, payroll, benefit, dan overtime jelas?
- Apakah tipe kerja contract/intern/freelance punya konsekuensi contract yang jelas?
- Apakah perubahan employment type dikoordinasikan dengan Employee Contracts/Movements?

Acceptance review:

- Employment type bisa dipakai oleh contract dan payroll future.
- Guard lintas contract terdokumentasi/teruji.

## Modul 7 — HR Reference Data

Pertanyaan review:

- Apakah category registry cukup untuk document type, gender, marital status, education, religion, bank, dan blood type?
- Apakah key unik per category?
- Apakah category yang sedang dipakai tidak bisa dihapus?
- Apakah reference yang sensitif seperti bank tidak membocorkan nomor rekening employee?

Acceptance review:

- Reference data bisa dipakai lintas HR tanpa category liar.
- Category CRUD aman.

## Modul 8 — Organization Structures

Pertanyaan review:

- Apakah hierarchy bebas circular reference?
- Apakah node parent/child aktif terlindungi saat archive/delete?
- Apakah relasi node ke departement/position jelas?
- Apakah siap menjadi dasar approval line future?

Acceptance review:

- Tree/hierarchy deterministic.
- Guard circular dan child aktif tersedia.

## Output Tiap Review

Setelah tiap modul selesai ditelusuri:

- Update `docs/projects/hr/<module>/tasks.md`.
- Tambahkan checkpoint/review note jika ada temuan penting.
- Jika ada koreksi kode, commit terpisah per modul.
- Catat deferred items di `docs/projects/mvp-status.md` bila berdampak ke roadmap.

## Quality Gate Minimal Per Modul

```bash
php artisan test --filter=<NamaModul>
php artisan module:validate
npm run typecheck
npm run build
git diff --check
```

Jika modul menyentuh frontend cukup besar:

```bash
npm run lint:check
npm run format:check
```

## Definition of Done

- Modul punya dokumentasi project yang sinkron dengan kode aktual.
- Permission dan destructive actions punya denial matrix.
- Tidak ada orphan data untuk relation penting.
- Soft delete/restore/force-delete jelas.
- UI bisa dipakai human-user tanpa kebingungan utama.
- Tidak ada blocker untuk Attendance terkait master data modul tersebut.
