# perubahan klasifikasi

klasifikasikan setiap diusulkan perubahan sebelum perencanaan atau coding.

## Levels

| tingkat | Contoh Umum contoh | Minimum dokumentasi | Persetujuan |
|---|---|---|---|
| `TRIVIAL` | Typo, komentar, lokal rename, tanpa perubahan perilaku formatting | Task catat, verifikasi bukti | AI/self-review kecuali dilindungi area |
| `STANDARD` | kecil fitur, terisolasi bug, terbatas refactor | work item ringkas, tasks, pengujian, penyelesaian laporan | work item pemilik |
| `SIGNIFICANT` | baru module, schema/API perubahan, eksternal integrasi, lintas-boundary refactor | lengkap paket, dampak penilaian, ADR ketika arsitektural | manusia persetujuan wajib |
| `CRITICAL` | Authentication/authorization, destruktif migrasi, breaking API, kepatuhan, berbiaya tinggi infrastruktur | lengkap paket, threat/risiko review, rollback, rilis gate | ditunjuk manusia pemberi persetujuan wajib |

## klasifikasi pertanyaan

- itu alter externally teramati perilaku?
- itu cross module atau boundary ownership?
- itu modify persistent data atau migrasi path?
- itu perubahan publik kontrak, keamanan, privasi, kepatuhan, biaya, atau ketersediaan?
- adalah rollback difficult, destruktif, atau sensitif waktu?
- itu menambahkan baru dependensi atau eksternal vendor?

pilih tertinggi berlaku tingkat. catat rasional di work item metadata.

## Eskalasi aturan

 pekerjaan item boleh menjadi dinaikkan at apa pun waktu. itu boleh tidak menjadi diturunkan setelah implementasi dimulai tanpa tertulis rasional dan manusia persetujuan.
