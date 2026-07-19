# Checkpoint C — Observability Boundary

Tanggal checkpoint: 2026-07-19  
Status: pass untuk dokumentasi baseline Console Task 06–08

Checkpoint ini mengunci hasil telusur area observability Console: Activity Center, Audit Logs, dan Login Activities. Tujuannya memastikan observability module bersifat read-only dari sisi user, route terlindungi permission, payload aman dari secret leakage, dan residual privacy/retention gap terdokumentasi sebelum lanjut ke runtime operation module seperti Queue Monitor dan Scheduler Monitor.

## Scope checkpoint

Dokumen yang menjadi input:

1. [06 — Activity Center Read Model](06-activity-center-read-model.md)
2. [07 — Audit Logs Immutable Boundary](07-audit-logs-immutable-boundary.md)
3. [08 — Login Activities Security Observability](08-login-activities-security-observability.md)

Area source yang menjadi fokus:

- `Console.ActivityCenters`;
- `Console.AuditLogs`;
- `Console.LoginActivities`;
- shared Inertia props `activity_center`;
- auth controller recording login/logout;
- audit service sanitizer;
- read-only observability route;
- privacy metadata seperti actor email, login email, IP address, dan user agent.

## Kesimpulan

Checkpoint C dinyatakan pass.

Observability boundary sudah memenuhi baseline:

- Activity Center hanya membaca `AuditLog` terbaru dan menulis marker baca user sendiri `activity_center_read_at`;
- Audit Logs hanya memiliki user-facing route read-only `GET /audit-logs`;
- Login Activities hanya memiliki user-facing route read-only `GET /login-activities`;
- route observability protected oleh permission:
  - `activity-center.view`;
  - `audit-logs.view`;
  - `login-activities.view`;
- unauthorized access ditolak oleh policy/authorization;
- AuditLogService sudah me-redact key sensitif pada `old_values`/`new_values` secara recursive;
- login sukses, login gagal, dan logout tercatat;
- submitted password tidak tersimpan di Login Activities;
- frontend observability hanya list/filter/detail, tanpa destructive action;
- tidak ada blocking security/correctness issue untuk lanjut ke Queue Monitor.

## Acceptance criteria checkpoint

- [x] Task 06–08 selesai.
- [x] Activity/audit/login logs aman dari secret leakage.
- [x] Read-only observability route terlindungi permission.

## Evidence

```bash
php artisan test --filter="ActivityCenter|AuditLog|LoginActivity"
```

Hasil:

- 13 tests passed;
- 43 assertions.

```bash
vendor/bin/pint --test app/Modules/Console/ActivityCenters app/Modules/Console/AuditLogs app/Modules/Console/LoginActivities tests/Feature/ActivityCenterTest.php tests/Feature/AuditLogTest.php tests/Feature/LoginActivityTest.php resources/js/components/activity-center-dropdown.tsx resources/js/pages/console/audit-logs resources/js/pages/console/login-activities
```

Hasil:

- passed.

```bash
php artisan module:validate
```

Hasil:

- all module contracts are valid.

```bash
npm run typecheck
```

Hasil:

- TypeScript compile check lulus.

```bash
npm run build
```

Hasil:

- Vite production build lulus.

```bash
git diff --check
```

Hasil:

- pass.

## Threat model ringkas

| Threat | Status | Boundary |
|---|---|---|
| User tanpa permission melihat Activity Center | tertutup | shared props fallback kosong; dropdown hidden |
| User tanpa permission mark Activity Center read | tertutup | route `activity-center.read` abort 403 |
| Activity Center mengubah data bisnis | tertutup | hanya update `users.activity_center_read_at` |
| User tanpa permission melihat Audit Logs | tertutup | policy `audit-logs.view` |
| User mengubah/menghapus Audit Logs dari UI | tertutup | tidak ada mutation route/controller |
| Audit old/new values menyimpan password/token/secret | tertutup untuk key sensitif | sanitizer recursive `[redacted]` |
| Audit description berisi secret dari caller | residual risk | guideline/review caller diperlukan |
| User tanpa permission melihat Login Activities | tertutup | policy `login-activities.view` |
| Login Activities menyimpan submitted password | tertutup | service tidak memakai password; regression test tersedia |
| Logout tidak tercatat | tertutup | `recordLogout()` dipanggil sebelum session invalidate |
| PII email/IP/user-agent tersimpan terlalu lama | residual risk | retention/minimization policy belum ada |

## Residual risk dan guide-plan

Tidak ada blocker sebelum lanjut ke Task 09. Follow-up yang tetap dicatat:

1. Retention dan archival observability.
   - Kondisi sekarang: audit logs dan login activities belum punya retention/prune/archive policy.
   - Guide-plan: putuskan umur simpan default, lalu buat command prune/archive jika diperlukan.

2. PII minimization.
   - Kondisi sekarang: actor email, login email, IP address, dan user agent tampil di UI.
   - Guide-plan: tentukan apakah IP/email perlu masking parsial untuk role tertentu atau setelah periode tertentu.

3. Audit description safety.
   - Kondisi sekarang: sanitizer recursive berlaku pada old/new values, bukan `description`.
   - Guide-plan: tambahkan guideline caller audit; pertimbangkan sanitizer description jika ditemukan pola secret.

4. Last login read model.
   - Kondisi sekarang: User Management belum memakai Login Activities untuk `lastLogin`.
   - Guide-plan: setelah observability semantics stabil, hubungkan `lastLogin` ke event `login` sukses terakhir.

5. Tamper-evident audit chain.
   - Kondisi sekarang: audit immutable pada application-level, bukan tamper-evident cryptographic chain.
   - Guide-plan: evaluasi setelah backup/restore dan compliance need lebih jelas.

## Keputusan checkpoint

Lanjut ke Task 09 — Queue Monitor runtime control.

Alasannya: observability read model, audit trail, dan login monitoring sudah permission-gated, read-only dari sisi user, dan punya test coverage yang cukup. Sisa temuan adalah privacy/retention/compliance roadmap, bukan blocker untuk menelusuri runtime operation module berikutnya.
