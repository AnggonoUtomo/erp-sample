# Software Engineering Operating System (SEOS)

SEOS adalah baseline dokumentasi dan workflow software engineering yang reusable dan AI-friendly. SEOS ditempatkan langsung di dalam repository dan mengatur pekerjaan mulai dari bootstrap proyek, perancangan, evolusi arsitektur, implementasi incremental, verifikasi, review, rilis, operasional, hingga baseline historis.

## Masalah yang Diselesaikan SEOS

- Mencegah AI atau developer mulai coding sebelum scope dan kriteria penerimaan siap.
- Menangani pekerjaan baru yang ditemukan saat development berlangsung.
- Menentukan kedalaman dokumentasi berdasarkan jenis perubahan dan tingkat risikonya.
- Memisahkan sumber kebenaran proyek saat ini dari catatan perubahan historis.
- Membuat klaim penyelesaian berbasis bukti dan dapat ditelusuri.
- Mengendalikan perubahan arsitektur, keamanan, data, dependensi, dan breaking change melalui human gate.

## Mulai dari Sini

1. Baca `AGENTS.md`.
2. Jalankan `docs/00-governance/AI-PROJECT-BOOTSTRAP-PROMPT.md`.
3. Isi dokumen baseline proyek tanpa melakukan coding.
4. Daftarkan pekerjaan baru di `docs/07-work-items/WORK-ITEM-REGISTRY.md`.
5. Klasifikasikan perubahan menggunakan `docs/00-governance/CHANGE-CLASSIFICATION.md`.
6. Salin paket yang sesuai dari `docs/07-work-items/templates/`.
7. Penuhi Definition of Ready sebelum coding dan Definition of Done sebelum pekerjaan dinyatakan selesai.

## Paket Work Item

- `feature`
- `architecture-change`
- `bug-fix`
- `refactoring`
- `integration`
- `data-migration`
- `dependency-upgrade`
- `security-change`
- `performance-improvement`
- `deprecation-removal`
- `incident-follow-up`

Setiap paket menyediakan dokumen pra-pengerjaan, rencana implementasi, validasi pasca-pengerjaan, context pack untuk AI, catatan deviasi, dan manifest bukti.

## Contoh Evolusi Arsitektur

Jika boundary `AccessControl` baru ditemukan ketika task lain sedang dikerjakan:

1. Hentikan task pada kondisi repository yang valid.
2. Daftarkan work item, misalnya `ARC-ACL-001`.
3. Salin paket `architecture-change`.
4. Lengkapi discovery record, boundary proposal, impact assessment, dan ADR.
5. Dapatkan persetujuan manusia yang diwajibkan.
6. Buat task module/fitur turunan dan implementasikan secara incremental.
7. Validasi arah dependensi dan kontrak publik.
8. Perbarui Boundary Registry, Module Catalog, kontrak, dan Evolution Log.

## Baseline dan Riwayat Pekerjaan

- `docs/01`–`06`, `08`–`09`, serta registry arsitektur menjelaskan kondisi proyek yang berlaku saat ini.
- `docs/07-work-items` menyimpan alasan, proses, bukti, dan riwayat setiap perubahan.
- `docs/11-baselines` menyimpan snapshot release/milestone yang bersifat historis.

## Aturan Bahasa

Semua dokumentasi SEOS dan dokumentasi proyek yang dibuat berdasarkan baseline ini wajib menggunakan Bahasa Indonesia. Istilah teknis boleh dipertahankan dalam bahasa aslinya ketika lebih tepat dan tidak ambigu.

## Integrasi Agent Skills

SEOS merutekan skill sesuai fase lifecycle. Skill membantu pelaksanaan pekerjaan; dokumen repository tetap menjadi sumber kebenaran. Skill eksternal dipasang dan dipelihara secara terpisah.
