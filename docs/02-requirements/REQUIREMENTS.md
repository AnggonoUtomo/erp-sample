# Requirement Catalog

## Fungsional Requirement

| ID | Pernyataan | Sumber | Prioritas | Kriteria Penerimaan | Status |
|---|---|---|---|---|---|
| FR-001 | Sistem harus dapat mengelola data karyawan (CRUD) | HR Dept | Must Have | Employee data dapat dibuat, dibaca, diubah, diarsipkan | Draft |
| FR-002 | Sistem harus dapat membuat workflow onboarding otomatis | HR Dept | Must Have | Onboarding checklist ter-generate saat employee dibuat | Draft |
| FR-003 | Sistem harus dapat membuat workflow offboarding otomatis | HR Dept | Must Have | Offboarding checklist ter-generate saat resignation/termination | Draft |
| FR-004 | Sistem harus dapat track dokumen karyawan | HR Dept | Must Have | Dokumen dapat diupload, track expiry, alert terkirim | Draft |
| FR-005 | Sistem harus dapat mengelola kontrak karyawan | HR Dept | Should Have | Kontrak dapat dibuat, track expiry, renewal workflow | Draft |
| FR-006 | Sistem harus dapat mengelola struktur organisasi | HR Dept | Must Have | Org chart dapat dibuat, posisi dan departemen terdefinisi | Draft |
| FR-007 | Sistem harus dapat mengelola dokumen perusahaan | Admin | Should Have | Dokumen CRUD, version control, approval workflow | Draft |
| FR-008 | Sistem harus memiliki RBAC | Security | Must Have | Role dan permission dapat dikonfigurasi | Draft |
| FR-009 | Sistem harus memiliki audit trail | Compliance | Should Have | Semua transaksi tercatat di audit log | Draft |
| FR-010 | Sistem harus dapat generate report | Management | Should Have | Report dapat di-export PDF/Excel | Draft |
| FR-011 | Sistem harus mengirim notifikasi | Product | Should Have | Email dan in-app notification terkirim | Draft |
| FR-012 | Sistem harus memiliki module generator | Engineering | Should Have | php artisan make:module generate DDD-Lite structure | Draft |

## Non-Fungsional Requirement

| ID | Kualitas Atribut | Terukur Requirement | Verifikasi | Status |
|---|---|---|---|---|
| NFR-001 | Performance | API response time <200ms untuk 95th percentile | Load test dengan 1000 concurrent users | Draft |
| NFR-002 | Availability | 99.9% uptime | Monitoring dengan uptime tracker | Draft |
| NFR-003 | Scalability | Support 1000 concurrent users | Load testing | Draft |
| NFR-004 | Security | OWASP Top 10 compliant | Security audit | Draft |
| NFR-005 | Maintainability | Test coverage >85% | PHPUnit coverage report | Draft |
| NFR-006 | Reliability | Zero data loss on failure | Disaster recovery test | Draft |
| NFR-007 | Usability | Page load time <2s | Browser DevTools Lighthouse | Draft |
| NFR-008 | Compatibility | Support modern browsers (Chrome, Firefox, Safari, Edge) | Cross-browser testing | Draft |

## Aturan Bisnis

| ID | Aturan | Berlaku Untuk | Exceptions | Bukti/Pengujian |
|---|---|---|---|---|
| BR-001 | Employee number harus unik | Employee creation | - | Unit test uniqueness |
| BR-002 | Onboarding harus diselesaikan sebelum employee active | Employee activation | Executive hire (fast track) | Integration test |
| BR-003 | Dokumen expiry harus dialert 30 hari sebelum | Document management | Permanent documents | Unit test alert timing |
| BR-004 | Offboarding harus complete sebelum employee exit | Employee termination | Emergency termination | Integration test |
| BR-005 | Kontrak expiry harus dialert 60 hari sebelum | Contract management | Indefinite contract | Unit test alert timing |
| BR-006 | Hanya HR Manager yang bisa approve resignation | Offboarding workflow | Auto-approval untuk probation | Policy test |
| BR-007 | Dokumen rahasia hanya bisa diakses oleh authorized role | Document access | Legal request | RBAC test |

## Batasan

1. Single database untuk semua modul
2. PHP 8.2+ required
3. Laravel 12 only
4. React 19 + TypeScript for frontend
5. No mobile native app (web only)
6. Single company deployment (no multi-tenant)

## Asumsi

1. Tim familiar dengan Laravel dan React
2. Deployment ke cloud dengan CI/CD
3. Queue worker available untuk async jobs
4. Email service configured
5. File storage configured (local or S3)

## Pertanyaan Terbuka

1. Apakah perlu integrasi dengan sistem payroll eksternal?
2. Apakah perlu multi-language support?
3. Apakah perlu real-time notification (WebSocket)?
4. Berapa maksimal jumlah employee yang disupport?
5. Apakah perlu backup dan restore automation?
