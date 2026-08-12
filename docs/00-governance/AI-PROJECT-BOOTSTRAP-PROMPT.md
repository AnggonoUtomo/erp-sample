# AI proyek bootstrap prompt

gunakan ini prompt sekali setelah menyalin template ke dalam proyek.

---

Anda adalah menginisialisasi proyek's engineering dokumentasi sistem.

## Mandatory instruksi

1. baca `AGENTS.md` dan semua file di `docs/00-governance/`.
2. periksa repository sebelum menulis proyek fakta:
 - languages dan frameworks;
 - paket/dependensi manifests;
 - sumber directories;
 - modules atau terbatas contexts;
 - database dan migrasi structure;
 - API/routes;
 - pengujian dan kualitas tools;
 - CI/CD dan deployment file;
 - eksisting dokumentasi;
 - saat ini Git status ketika tersedia.
3. terpisah temuan ke dalam:
 - **diverifikasi fakta** — secara langsung supported oleh repository bukti;
 - **Asumsi** — plausible tetapi unverified;
 - **Pertanyaan Terbuka pertanyaan** — dibutuhkan keputusan atau missing information.
4. tidak hasilkan arsitektur semata-mata dari preferensi. pertahankan yang telah ada proyek pola kecuali perubahan adalah secara eksplisit disetujui.
5. isi baseline dokumen di ini urutan:
 1. `docs/01-product/PROJECT-BRIEF.md`
 2. `docs/01-product/SCOPE.md`
 3. `docs/01-product/PRD.md`
 4. `docs/02-requirements/REQUIREMENTS.md`
 5. `docs/03-architecture/SYSTEM-DESIGN.md`
 6. `docs/03-architecture/MODULE-CATALOG.md`
 7. `docs/04-design/DATABASE-DESIGN.md`
 8. `docs/04-design/API-SPEC.md`
 9. `docs/05-engineering/TECHNICAL-SPEC.md`
 10. `docs/05-engineering/TESTING-STRATEGY.md`
 11. `docs/06-planning/IMPLEMENTATION-PLAN.md`
 12. `docs/02-requirements/TRACEABILITY-MATRIX.md`
6. gunakan stabil ID dari `docs/00-governance/ID-CONVENTIONS.md`.
7. pertahankan setiap heading di setiap template. tulis `Not applicable` dengan alasan daripada daripada deleting bagian.
8. tambahkan sumber referensi sebagai repository path dan line ranges ketika practical.
9. tidak mulai coding.

## akhir bootstrap Output

hasilkan:

- repository inventaris;
- dokumen selesai;
- asumsi made;
- belum diselesaikan keputusan;
- dokumentasi inconsistencies found;
- recommended pertama fitur paket;
- readiness verdict: `NOT READY`, `CONDITIONALLY READY`, atau `READY FOR FEATURE PLANNING`.

---
