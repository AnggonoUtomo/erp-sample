---
id: REQ-CATALOG-001
title: Katalog Requirement Produk 12erp
document_type: requirement-catalog
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [DOC-PROJECT-BRIEF, PRD-001, SCOPE-001, TRACE-001]
---

# Katalog Requirement Produk 12erp

## Status Requirement

| Status | Makna |
|---|---|
| `approved` | bagian baseline produk aktif |
| `deferred` | kandidat; bukan komitmen implementasi |
| `observed` | perilaku kode yang tercatat, tetapi belum menjadi keputusan requirement mandiri |
| `superseded` | pernyataan lama digantikan atau dipindahkan ke sumber kebenaran yang tepat |

## Functional Requirements Aktif

| ID | Capability | Requirement | Prioritas | Kriteria penerimaan tingkat produk | Status |
|---|---|---|---|---|---|
| `FR-001` | `CAP-HR-001` | Sistem harus memungkinkan pengelola yang diotorisasi membuat, membaca, memperbarui, mengarsipkan, dan memulihkan data pegawai. | Must | operasi menjaga validasi identitas, referensi, serta state arsip; aksi yang tidak diizinkan ditolak | approved |
| `FR-002` | `CAP-HR-004` | Sistem harus mengelola onboarding berbasis template, instance, dan task dengan lifecycle eksplisit. | Must | state dan transisi dapat diverifikasi; penyelesaian/cancel/archive tidak melewati invariant yang berlaku | approved |
| `FR-003` | `CAP-HR-006` | Sistem harus mengelola offboarding berbasis template, instance, dan task sampai readiness/finalisasi. | Must | readiness dan finalisasi hanya terjadi melalui transisi valid serta aksi yang diotorisasi | approved |
| `FR-004` | `CAP-DOC-001` | Sistem harus mengelola metadata, verifikasi, attachment, dan status kedaluwarsa dokumen pegawai. | Must | dokumen dapat dicatat dan ditinjau status verifikasi/kedaluwarsanya; akses attachment/delivery terkontrol | approved |
| `FR-005` | `CAP-HR-003` | Sistem harus memelihara riwayat kontrak kerja dan lifecycle effective-dated. | Must | create, activate, terminate, cancel, supersede, archive/restore mengikuti state serta validasi yang berlaku | approved |
| `FR-006` | `CAP-HR-002` | Sistem harus mengelola struktur organisasi dan referensi tenaga kerja yang dibutuhkan lifecycle pegawai. | Must | referensi dapat dikelola tanpa merusak integritas relasi yang masih digunakan | approved |
| `FR-007` | `CAP-DOC-002` | Sistem harus mengelola ingestion, versioning, archive/restore, ownership, dan delivery dokumen privat/perusahaan. | Must | lifecycle dokumen serta delivery tidak melewati kontrol akses dan ownership yang berlaku | approved |
| `FR-008` | `CAP-ADM-001` | Sistem harus menyediakan authentication serta role/permission untuk membatasi aksi administratif dan bisnis. | Must | request tanpa authentication/authorization yang diperlukan ditolak; detail matriks ditetapkan baseline keamanan | approved |
| `FR-009` | `CAP-AUD-001` | Sistem harus menyediakan bukti audit untuk aksi penting dan visibilitas aktivitas login sesuai scope yang disetujui. | Should | record audit/aktivitas dapat ditelusuri dan hanya dibaca pengguna yang diotorisasi; coverage rinci ditetapkan per capability | approved |
| `FR-010` | `CAP-RPT-001` | Sistem harus menyediakan laporan HR read-only untuk headcount/status dan masa berlaku kontrak/dokumen. | Should | filter menghasilkan data read-only yang konsisten dengan sumber serta tidak melakukan mutation bisnis | approved |
| `FR-013` | `CAP-HR-005` | Sistem harus mengelola perubahan penugasan pegawai dengan before/after history dan lifecycle persetujuan/aplikasi. | Must | movement hanya dapat di-approve/apply/cancel melalui transisi valid dan tidak mengubah assignment sebelum aksi apply yang sah | approved |
| `FR-014` | `CAP-ADM-001` | Sistem harus menyediakan lifecycle user administratif, role assignment, serta impersonation yang terkendali. | Should | create/update/archive/restore/impersonation tunduk pada permission dan guard yang berlaku | approved |
| `FR-015` | `CAP-CFG-001` | Sistem harus memungkinkan administrator mengelola konfigurasi dan template sistem yang disetujui. | Should | nilai tervalidasi, perubahan sensitif diotorisasi, dan dampaknya dapat ditelusuri sesuai risiko | approved |
| `FR-016` | `CAP-OPS-001` | Sistem harus menyediakan operasi backup/restore, queue, dan scheduler sesuai kontrol serta batasan runtime. | Should | aksi tersedia hanya bagi operator yang diotorisasi, tervalidasi, dan mempunyai bukti hasil/gagal yang dapat ditinjau | approved |
| `FR-017` | `CAP-EXP-001` | Sistem harus menyediakan discovery dan activity surface yang menghormati akses pengguna. | Could | hasil search/activity tidak mengekspos data yang tidak boleh dibaca dan tidak melakukan mutation bisnis tak terduga | approved |

## Functional Requirements Deferred

| ID | Capability | Kandidat requirement | Syarat sebelum `approved` | Status |
|---|---|---|---|---|
| `FR-011` | `CAP-NOT-001` | Sistem mengirim notifikasi bisnis otomatis untuk trigger lifecycle atau expiry. | trigger, timing, recipient, channel, privacy, retry, idempotency, dan acceptance disetujui | deferred |
| `FR-018` | `CAP-SELF-001` | Employee menggunakan self-service untuk data atau dokumen pribadi. | use case, field exposure, verification, dan authorization disetujui | deferred |
| `FR-019` | `CAP-INT-001` | Sistem bertukar data dengan sistem eksternal. | sistem target, contract, owner, compatibility, security, failure mode, dan operasi disetujui | deferred |

## Non-Functional Requirements Aktif

Angka ambang tidak dicantumkan sampai tersedia baseline, kebutuhan bisnis, metode ukur, dan approval. Hal ini tidak menghapus kewajiban kualitas.

| ID | Atribut | Requirement | Verifikasi minimum per perubahan | Status |
|---|---|---|---|---|
| `NFR-001` | Performance | Perubahan tidak boleh menimbulkan regresi performa material pada flow yang terdampak. | ukur baseline dan hasil setelah perubahan dengan workload yang dijelaskan | approved |
| `NFR-002` | Availability | Perubahan runtime harus mempertahankan availability yang disepakati untuk lingkungan target. | definisikan failure/rollback dan ukur terhadap baseline operasional yang tersedia | approved |
| `NFR-003` | Scalability | Desain tidak boleh mengklaim kapasitas tanpa workload dan bukti; risiko pertumbuhan harus dicatat. | uji kapasitas hanya ketika skala target disetujui | approved |
| `NFR-004` | Security dan privacy | Data, dokumen privat, session, input, dan aksi sensitif harus dilindungi sesuai risiko. | authorization/security review dan test terfokus pada setiap perubahan relevan | approved |
| `NFR-005` | Maintainability | Perubahan harus mengikuti boundary/DDD-Lite yang berlaku, mempunyai test proporsional, dan dapat ditelusuri. | quality gate work item; coverage percentage hanya dipakai bila dibaselining | approved |
| `NFR-006` | Reliability dan recoverability | Mutation penting harus menjaga integritas data serta mempunyai strategi failure/rollback yang sesuai. | test transaksi/failure serta rehearsal recovery ketika risiko menuntut | approved |
| `NFR-007` | Usability dan accessibility | Flow pengguna harus dapat dipahami, memberikan feedback state/error, dan tidak menciptakan hambatan akses yang diketahui. | review UI/accessibility proporsional pada perubahan frontend | approved |
| `NFR-008` | Compatibility | Perubahan harus menjaga kontrak dan environment yang dinyatakan aktif atau menyediakan migration/deprecation plan. | compatibility test dan matrix environment ketika baseline tersedia | approved |

Target numerik untuk seluruh atribut di atas dikelola melalui `CAND-QUAL-001` sampai dipromosikan menjadi work item.

## Perilaku Teramati yang Memerlukan Validasi Produk

Tabel ini menjaga baseline kompatibilitas, tetapi tidak menaikkan perilaku kode menjadi aturan produk secara otomatis. Perubahan perilaku tetap memerlukan work item; promosi menjadi `approved` memerlukan validasi bisnis eksplisit.

| ID | Perilaku/invariant yang teramati | Status keputusan | Bukti/validasi |
|---|---|---|---|
| `BR-001` | Identitas pegawai yang digunakan sebagai nomor bisnis dijaga unik dalam scope organisasi. | observed | validation dan database constraint/test yang relevan |
| `BR-002` | Aksi lifecycle dibatasi oleh state dan authorization yang berlaku. | observed | policy/guard serta state transition test per capability |
| `BR-003` | Tipe dokumen yang mensyaratkan masa berlaku mewajibkan tanggal kedaluwarsa yang valid. | observed | request validation dan test dokumen |
| `BR-004` | Delivery dokumen privat melewati ownership dan access decision. | observed | contract/security test pada ingestion/delivery |
| `BR-005` | Laporan dan discovery lintas sumber bersifat read-only terhadap data bisnis pemilik. | observed | review query/contract serta regression test |

Timing alert, exception approval, dan rule historis lain yang belum disetujui tetap merupakan perilaku teramati atau kandidat; bukan aturan produk aktif hanya karena pernah tertulis atau ada default dalam kode.

## Disposisi Pernyataan Lama

| Pernyataan/ID lama | Disposisi | Sumber kebenaran baru |
|---|---|---|
| target jumlah module pada `REQ-001` lama | superseded sebagai requirement produk | `MODULE-CATALOG.md` untuk fakta arsitektur; capability pada PRD/Scope |
| `REQ-002` struktur DDD-Lite | dipindahkan dari requirement produk | ADR-0001 dan `ARC-DDD-LITE-001` |
| `REQ-008`/`FR-012` module generator | dipindahkan dari requirement produk | engineering tooling pada `ARC-DDD-LITE-001` |
| target coverage, response, uptime, kapasitas, page load, dan zero data loss lama | menjadi kandidat target | `CAND-QUAL-001` |
| fase rilis lama | superseded sebagai komitmen | work item aktif dan readiness |

## Pertanyaan Terbuka

Prioritas rilis, persona rinci, authorization matrix, target kualitas, external integration, employee self-service, dan notifikasi otomatis dikelola sebagai kandidat/work item terpisah.

## Persetujuan

```yaml
gate: requirement-baseline-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - requirement rinci baru tetap mengikuti workflow SEOS
  - bukti kode tidak mengesahkan requirement baru secara otomatis
evidence:
  - SPEC-FTR-PROD-001
```
