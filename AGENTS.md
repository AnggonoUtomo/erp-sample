# AGENTS.md — Aturan Operasional AI SEOS

## Misi

Kelola repository ini menggunakan SEOS. Hasilkan perubahan yang kecil, dapat ditelusuri, dapat diverifikasi, dan mudah direview. Jangan pernah menggantikan bukti dengan klaim yang terdengar meyakinkan.

## Urutan Baca Wajib

1. `docs/00-governance/SEOS-MANIFEST.md`
2. `docs/00-governance/DOCUMENTATION-STANDARD.md`
3. `docs/00-governance/CHANGE-CLASSIFICATION.md`
4. `docs/00-governance/WORK-ITEM-LIFECYCLE.md`
5. `docs/00-governance/HUMAN-DECISION-GATES.md`
6. Work item aktif beserta `CONTEXT-PACK.md`
7. Requirement, ADR, boundary, kontrak, dan standar engineering yang relevan

## Prioritas Sumber Kebenaran

1. Work item dan task aktif yang telah disetujui
2. ADR yang telah diterima dan kontrak eksplisit
3. Dokumen baseline yang berlaku saat ini
4. Standar engineering dan quality gate
5. Implementasi kode eksisting
6. Catatan historis work item

Jika terjadi konflik, buat catatan deviasi atau keputusan. Jangan menyelaraskan konflik secara diam-diam.

## Workflow Wajib

`DISCOVER → CLASSIFY → REGISTER → PROPOSE → APPROVE → READY → IMPLEMENT → VERIFY → REVIEW → COMPLETE → BASELINE-SYNC`

## Sebelum Coding

- Klasifikasikan pekerjaan sebagai `TRIVIAL`, `STANDARD`, `SIGNIFICANT`, atau `CRITICAL`.
- Daftarkan pekerjaan non-trivial di `WORK-ITEM-REGISTRY.md`.
- Pilih dan salin paket work item yang sesuai.
- Isi dokumen pra-pengerjaan dengan fakta, asumsi, risiko, scope, dan kriteria penerimaan.
- Dapatkan persetujuan manusia untuk keputusan yang masuk Human Decision Gate.
- Penuhi Definition of Ready.
- Buat `CONTEXT-PACK.md` khusus untuk task aktif.

## Selama Coding

- Kerjakan satu task aktif pada satu waktu.
- Patuhi file/area yang diizinkan dan dilarang serta aturan boundary.
- Implementasikan vertical slice terkecil yang dapat diuji.
- Jalankan pemeriksaan terfokus sebelum memperluas scope.
- Catat fakta arsitektur, perilaku, atau batasan baru yang ditemukan.
- Jangan menyelipkan fitur ke dalam refactor atau breaking change ke dalam bug fix.

## Pekerjaan yang Muncul Saat Development

Ketika ditemukan scope, module, boundary, kontrak, migrasi, risiko keamanan, atau dependensi baru:

1. Hentikan pekerjaan pada kondisi repository yang valid.
2. Catat penemuan dan hubungkan melalui `discovered_by`.
3. Klasifikasikan dan daftarkan pekerjaan baru.
4. Tentukan apakah task aktif diblokir, perlu dipecah, atau aman dilanjutkan.
5. Gunakan paket work item yang sesuai; penemuan arsitektur menggunakan `architecture-change`.
6. Buat ADR dan minta persetujuan manusia bila diwajibkan.
7. Lanjutkan implementasi hanya setelah pekerjaan baru memenuhi Definition of Ready.

## Setelah Coding

- Buat dokumen verifikasi, review, dan penyelesaian yang diwajibkan paket work item.
- Lengkapi `EVIDENCE-MANIFEST.md` dengan perintah dan hasil aktual.
- Catat deviasi; jangan menulis ulang riwayat agar terlihat seolah implementasi selalu sesuai rencana awal.
- Terapkan `DOCUMENTATION-SYNC-MATRIX.md`.
- Perbarui dokumen baseline dan registry yang terdampak.
- Penuhi Definition of Done sebelum status menjadi `completed`.

## Pembuatan Dokumen Baru

AI boleh membuat instance dari template yang telah disetujui. AI tidak boleh menciptakan kategori top-level atau template reusable baru tanpa `DOCUMENT-PROPOSAL.md` dan persetujuan yang diperlukan. Hindari sumber kebenaran ganda.

## Routing Skill

- Ide ambigu: `idea-refine` / workflow interview
- Spesifikasi proyek atau fitur: `spec-driven-development`
- Perencanaan task: `planning-and-task-breakdown`
- Persiapan konteks: `context-engineering`
- Coding incremental: `incremental-implementation`
- Perilaku test-first: `test-driven-development`
- Bug/insiden: `debugging-and-error-recovery`
- API/kontrak: `api-and-interface-design`
- Frontend: `frontend-ui-engineering`
- Arsitektur/ADR: `documentation-and-adrs`
- Review: `code-review-and-quality`
- Keamanan: `security-and-hardening`
- Performa: `performance-optimization`
- Delivery: `ci-cd-and-automation`, `shipping-and-launch`

Skill eksternal membantu pelaksanaan pekerjaan, tetapi tidak boleh menimpa aturan atau sumber kebenaran SEOS.

## Larangan Klaim Tanpa Bukti

Jangan menyatakan pekerjaan selesai, aman, kompatibel, telah diuji, atau siap produksi tanpa bukti yang sesuai dan pemeriksaan yang disebutkan secara eksplisit. Pemeriksaan yang dilewati atau tidak tersedia wajib dilaporkan.

## Aturan Bahasa Dokumentasi

- Seluruh dokumentasi wajib menggunakan Bahasa Indonesia.
- Istilah teknis, identifier, command, path, nama library/framework, protocol, enum, dan nama pola boleh dipertahankan dalam bentuk aslinya jika penerjemahan mengurangi ketepatan.
- AI dan developer dilarang membuat dokumentasi baru berbahasa Inggris kecuali diwajibkan sistem/vendor eksternal. Alasan pengecualian wajib dicatat.
- Saat dokumen lama yang masih berbahasa Inggris disentuh atau direvisi, bagian yang relevan wajib diterjemahkan ke Bahasa Indonesia.
- `docs/00-governance/DOCUMENTATION-STANDARD.md` adalah sumber aturan bahasa dokumentasi.
