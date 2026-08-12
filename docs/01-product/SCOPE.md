# Project Scope

## Dalam Scope

| ID | Kapabilitas | Rasional | Rilis/Fase |
|---|---|---|---|
| CAP-001 | HR Employee Management | Core HR data management | Phase 1 |
| CAP-002 | HR Onboarding Workflow | Automated employee onboarding | Phase 2 |
| CAP-003 | HR Offboarding Workflow | Automated employee offboarding | Phase 2 |
| CAP-004 | HR Document Management | Employee document tracking | Phase 2 |
| CAP-005 | HR Contract Management | Employment contract lifecycle | Phase 2 |
| CAP-006 | Organization Structure | Org chart, positions, departments | Phase 1 |
| CAP-007 | Document Management System | Company-wide document management | Phase 3 |
| CAP-008 | RBAC & Authorization | Role-based access control | Phase 1 |
| CAP-009 | Audit Logging | Track all important actions | Phase 2 |
| CAP-010 | Reporting & Dashboard | HR and document analytics | Phase 4 |
| CAP-011 | Notification System | Email and in-app notifications | Phase 3 |
| CAP-012 | Module Generator | DDD-Lite module scaffolding | Phase 1 |
| CAP-013 | User Management | User accounts, impersonation | Phase 2 |
| CAP-014 | System Settings | System configuration, email, branding | Phase 2 |
| CAP-015 | Backup & Restore | Database backup and restore | Phase 3 |
| CAP-016 | Access Control | Access policies and restrictions | Phase 3 |
| CAP-017 | Activity Center | Activity tracking across modules | Phase 3 |
| CAP-018 | Global Search | Cross-module search | Phase 3 |
| CAP-019 | Login Monitoring | Login activity tracking | Phase 3 |
| CAP-020 | Queue Monitoring | Queue health monitoring | Phase 3 |
| CAP-021 | Scheduler Monitoring | Scheduled task monitoring | Phase 3 |

## Di Luar Scope

| Item | Alasan | Peninjauan Ulang Memicu |
|---|---|---|
| Mobile app native | Fokus web app dulu | User demand >50% mobile usage |
| Multi-tenant SaaS | Single company deployment | Business model change |
| Payroll processing | Kompleksitas tinggi, regulasi khusus | Dedicated payroll module request |
| Performance management | Bukan prioritas saat ini | Annual review cycle requirement |
| Recruitment/ATS | Sistem terpisah sudah ada | Integrasi ATS eksisting |
| Learning Management | Bukan core HR | LMS integration request |
| Time & attendance | Sistem terpisah sudah ada | Integration request |

## Boundaries

- Sistem boundary: Web application dengan React frontend dan Laravel backend
- Organizational boundary: Internal company use, tidak untuk client eksternal
- Data boundary: Single database, shared antara semua modul (28 modul)
- Integrasi boundary: API internal antar modul melalui kontrak, tidak ada external API untuk saat ini

## Ruang Lingkup Perubahan Proses

Usulan scope perubahan wajib dokumen nilai, biaya, risiko, terdampak requirement, terdampak arsitektur, dan rilis dampak sebelum persetujuan.

Proses perubahan scope:
1. Ajukan perubahan dalam format written proposal
2. Impact assessment oleh technical lead
3. Review oleh product owner
4. Approval atau rejection dengan documented rationale
5. Update dokumen terkait jika approved
