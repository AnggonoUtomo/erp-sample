# SOP Release dan Dev Workflow

Dokumen ini menjadi SOP kerja harian setelah branch project distabilkan menjadi `main` dan `dev`.

## Branch Policy

- `main`: branch utama/stabil.
- `dev`: branch kerja harian.
- Feature besar sebaiknya dibuat dari `dev` jika butuh isolasi.
- Jangan force push ke `main` kecuali ada alasan history repair yang eksplisit disetujui.

## Alur Kerja Harian

1. Pastikan berada di branch `dev`.
2. Tarik perubahan terbaru bila diperlukan.
3. Kerjakan satu task kecil.
4. Jalankan quality gate sesuai area yang disentuh.
5. Review diff.
6. Commit dengan pesan jelas.
7. Push ke `origin/dev`.

Contoh:

```bash
git status
git switch dev
git pull origin dev
```

## Aturan Commit

Gunakan format:

```txt
<type>: <ringkasan singkat>
```

Tipe yang dipakai:

- `feat`: fitur baru.
- `fix`: bug fix.
- `refactor`: perubahan struktur tanpa behavior baru.
- `test`: test baru/perubahan test.
- `docs`: dokumentasi.
- `chore`: tooling/config non-feature.

Contoh:

```bash
git commit -m "feat: add dashboard operational overview"
git commit -m "docs: add hr foundation review plan"
```

## Migration Workflow

Sebelum membuat migration:

- Pastikan nama tabel mengikuti namespace module.
- Pastikan migration tidak menabrak existing table.
- Pastikan rollback aman.
- Pastikan field sensitif encrypted/masked bila diperlukan.
- Pastikan foreign key dan index sesuai query utama.

Setelah migration:

```bash
php artisan migrate:fresh --seed
php artisan test
```

Untuk database lokal yang tidak ingin dihapus, jalankan targeted migration secara hati-hati dan backup dulu.

## Seeder Workflow

Seeder harus:

- Idempotent bila memungkinkan.
- Menghasilkan data yang nyambung antar modul.
- Tidak membuat akun/role/permission duplikat.
- Tidak menyimpan secret produksi.
- Menjelaskan password akun demo hanya untuk local/dev.

Urutan seed ideal:

1. Console roles dan permissions.
2. System settings baseline.
3. User baseline.
4. HR foundation master data.
5. Employees.
6. Contracts, documents, movements.
7. Onboardings/offboardings.
8. Reports/lifecycle sample.

## Quality Gate

Untuk perubahan dokumentasi saja:

```bash
git diff --check
```

Untuk perubahan backend:

```bash
vendor/bin/pint --test
php artisan module:validate
php artisan test --filter=<RelevantTest>
git diff --check
```

Untuk perubahan frontend:

```bash
npm run format:check
npm run lint:check
npm run typecheck
npm run build
git diff --check
```

Untuk perubahan lintas backend/frontend:

```bash
vendor/bin/pint --test
php artisan module:validate
npm run format:check
npm run lint:check
npm run typecheck
npm run build
php artisan test
git diff --check
```

## Deploy Lokal / Verifikasi Human-User

1. Jalankan migration/seed lokal.
2. Login sebagai user sesuai role.
3. Cek sidebar dan permission.
4. Jalankan workflow human-user utama.
5. Cek audit log dan activity center.
6. Cek tidak ada error di browser console.
7. Cek `storage/logs/laravel.log` untuk error baru.

## Promosi dari dev ke main

Saat `dev` sudah stabil:

```bash
git switch main
git pull origin main
git merge dev
git push origin main
git switch dev
```

Jika ada conflict, selesaikan dengan membaca konteks file terkait, jalankan ulang quality gate, lalu commit merge.

## Rollback Lokal

Untuk membatalkan perubahan yang belum commit:

```bash
git status
git diff
```

Jangan memakai reset destruktif sebelum yakin tidak ada perubahan penting milik user.

Untuk membatalkan commit terakhir yang sudah dibuat tapi belum push:

```bash
git revert <commit>
```

Untuk rollback migration lokal:

```bash
php artisan migrate:rollback
```

## Hal yang Tidak Boleh Dilakukan

- Commit `.env`, secret, token, backup file, atau file log.
- Campur formatting massal dengan behavior change.
- Force delete data production tanpa backup dan dry-run.
- Membuat consumer Attendance/Payroll membaca table internal HR langsung.
- Menambah storage dokumen baru di HR.
- Menampilkan role `super-system` kepada actor non-super-system.
