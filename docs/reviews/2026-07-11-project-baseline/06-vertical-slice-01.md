# 06 — Incremental Implementation: Simulasi Vertical Slice 01

## Outcome slice

Menyediakan baseline test yang deterministic untuk discovery/generator module dan avatar lifecycle. Ini hanya runbook referensi; **belum diimplementasikan**.

## Scope

- Hanya TASK-01 dan TASK-02.
- Maksimum file yang disebut pada kedua task.
- Tidak mengubah manifest module produksi, frontend, database schema, atau behavior pengguna.

## Urutan eksekusi kelak

1. Rekam kegagalan focused test dan identifikasi ownership setiap temporary directory.
2. Buat failing regression test untuk root fixture unik/parallel collision.
3. Tambahkan seam root path paling kecil pada registry/generator.
4. Jalankan focused, serial ×3, parallel ×3.
5. Buat failing regression test lifecycle avatar disk.
6. Perbaiki fixture/setup saja; ubah production behavior hanya jika defect terbukti dan scope disetujui.
7. Jalankan full gates dan pastikan tree hanya berisi file scope.
8. Catat file berubah, alasan, command, hasil, concern, dan hal yang sengaja tidak disentuh.

## Acceptance slice

- 162 test yang saat audit ada dapat lulus tanpa collision (jumlah boleh bertambah karena regression tests).
- Tidak ada `TmpProject` atau media test residue.
- Re-run memberi hasil sama.
- Build dan checks tetap hijau setelah task quality-format terpisah; formatting massal tidak dicampur slice ini.

## File yang berubah pada audit dokumentasi ini

Tidak ada file aplikasi yang berubah. Hanya folder `docs/reviews/2026-07-11-project-baseline/` yang ditambahkan.

## Cara verifikasi kelak

`php artisan test --filter=SharedKernelTest`, `php artisan test --filter=MakeModuleCommandTest`, `php artisan test --filter=UserManagementTest`, full serial dan parallel, lalu `git status --short`.

