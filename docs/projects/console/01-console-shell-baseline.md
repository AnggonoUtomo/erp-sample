# 01 — Console Shell, Auth, dan Dashboard Baseline

Status: `Task 01 completed — 2026-07-19`.

Dokumen ini mencatat hasil telusur entry point Console: login, dashboard, layout, sidebar, header, theme, navigation, dan shared auth props. Sudut pandangnya tetap “seolah-olah Console baru dirancang”, tetapi evidence diambil dari implementasi aktual.

## Ringkasan keputusan aktual

Console adalah workspace administrasi utama. Route utama `/dashboard` merender `resources/js/pages/console/dashboard.tsx` dan hanya bisa diakses user authenticated. Guest diarahkan ke `/console/login`, sedangkan `/login` hanya redirect ke `/console/login`.

Project bisnis HR punya entry point sendiri (`/hr/login`, `/hr/dashboard`) tetapi tetap memakai session/auth Laravel yang sama. Ini menegaskan pemisahan konteks UI, bukan guard berbeda.

## Route dan auth entry point

File utama:

- `routes/web.php`
- `routes/auth.php`
- `resources/js/pages/console/auth/login.tsx`
- `tests/Feature/DashboardTest.php`

Behavior aktual:

- `/` merender `welcome`.
- `/dashboard` berada dalam middleware `auth` dan menjadi Console dashboard.
- `/hr/dashboard` berada dalam middleware `auth` + `can:hr.view`.
- `/login` redirect ke `/console/login`.
- `/console/login` memakai `AuthenticatedSessionController::create`.
- `/hr/login` memakai `AuthenticatedSessionController::createHr`.

Acceptance yang terpenuhi:

- guest yang membuka `/dashboard` diarahkan ke `/console/login`;
- authenticated user bisa membuka `/dashboard`;
- user dengan permission `hr.view` bisa membuka `/hr/dashboard`.

## Shared Inertia props

File utama:

- `app/Http/Middleware/HandleInertiaRequests.php`
- `resources/js/types/index.ts`
- `app/Models/User.php`

Shared props yang menjadi kontrak shell:

| Prop | Sumber | Kegunaan |
|---|---|---|
| `name` | branding app name | fallback nama aplikasi |
| `branding` | `SystemSettingService::brandingSettings()` | app name, logo, favicon |
| `localization` | `SystemSettingService::localizationSettings()` | format tanggal/waktu/timezone |
| `pagination` | `SystemSettingService::paginationSettings()` | default per page global |
| `navigation` | `ModuleRegistry::navigations()` | menu sidebar lintas module |
| `activity_center` | `ActivityCenterService::summaryFor()` bila berizin | dropdown activity center |
| `quote` | Laravel `Inspiring` | konten dekoratif |
| `flash` | session | success/error global |
| `auth.user` | `sharedUser()` | identity minimal login user |
| `auth.roles` | `User::getUserRoles()` | role map untuk UI/permission helper |
| `auth.permissions` | `User::getUserPermissions()` | permission map efektif |
| `auth.super` | `User::isSuperAdmin()` | flag role `super-system` |
| `auth.impersonation` | session impersonation | banner/menu impersonation |

Catatan security:

- `auth.user` hanya membawa `id`, `name`, `email`, `avatar`, dan timestamps.
- Password, remember token, dan credential tidak dibagikan.
- `activity_center` fallback kosong jika user tidak punya permission `activity-center.view`.

## Sidebar sebagai navigation shell

File utama:

- `resources/js/components/app-sidebar.tsx`
- `resources/js/components/ui/sidebar.tsx`
- `app/Support/Modules/ModuleRegistry.php`

Behavior aktual:

- Menu berasal dari module `navigation.php` melalui `ModuleRegistry::navigations()`.
- Sidebar membuat item `Dasbor` lokal untuk `/dashboard` atau `/hr/dashboard` sesuai URL aktif.
- Group navigation menjadi dropdown kategori.
- Item difilter di frontend memakai `permissions` dan `usePermission().canAny()`.
- Active state mendukung exact match, dashboard special case, dan prefix URL.
- Tooltip collapsed sidebar menampilkan nested child menu.
- Scroll sidebar disimpan di `sessionStorage`.
- Footer sidebar berisi avatar profile dan icon settings: Profil, Kata Sandi, Tampilan.
- Menu title diterjemahkan ke Bahasa Indonesia dengan konteks domain.

Catatan boundary:

- Filtering sidebar adalah UX convenience, bukan security boundary. Route tetap harus dilindungi backend policy/middleware.
- Console boleh menampilkan link project bisnis jika user punya permission terkait, tetapi domain data tetap berada di project masing-masing.

## Header sebagai command bar

File utama:

- `resources/js/components/app-menu-header.tsx`

Behavior aktual:

- Header hanya muncul jika `auth.user` tersedia.
- Sidebar trigger dan breadcrumbs menjadi area kiri.
- Search bar tampil sebagai placeholder command/search (`Cari menu, data, laporan...`, `Ctrl K`).
- Activity Center dropdown tampil dari shared props/service.
- Button Help ada sebagai placeholder UI.
- Toggle light/dark memakai `useAppearance()`.
- User dropdown menampilkan avatar/name dan menu user.
- Header memakai background `bg-sidebar`; glass/backdrop hanya aktif saat sticky.

Gap yang dicatat:

- Global search/command palette belum aktif.
- Help button belum membuka dokumentasi/panduan.

Kedua gap ini tidak menghalangi Task 01 karena task ini adalah baseline telusur, tetapi perlu diputuskan sebelum Console dianggap polish-final.

## Dashboard Console

File utama:

- `resources/js/pages/console/dashboard.tsx`

Dashboard saat ini adalah landing operasional Console dengan:

- greeting berdasarkan `auth.user.name`;
- ringkasan module aktif dari `navigation`;
- jumlah permission efektif dari `auth.permissions`;
- jumlah role dari `auth.roles`;
- unread activity dari `activity_center.unread_count`;
- kartu readiness operasional;
- chart mini statis;
- project terbaru dan aktivitas terbaru.

Catatan maintainability:

- Summary cards memakai data runtime untuk module, permission, role, dan unread activity.
- Beberapa bagian lain masih statis/kurasi manual (`operations`, `recentActivities`, `projectRows`, chart mini).
- Bagian statis cocok sebagai MVP dashboard naratif, tetapi jika nanti menjadi dashboard operasional final perlu diganti menjadi read model/source-backed metrics.

## Theme/layout behavior

File terkait:

- `resources/js/hooks/use-appearance.tsx`
- `resources/css/app.css`
- `resources/js/components/app-menu-header.tsx`
- `resources/js/components/app-sidebar.tsx`

Behavior aktual:

- Appearance mendukung `light` dan `dark`.
- Color theme disimpan di `localStorage` dengan `data-theme`.
- Sidebar memakai token `--sidebar*` dan hover mengikuti `--accent`.
- Header mengikuti background sidebar dan glass hanya saat sticky.
- Console shell menjadi pattern visual untuk project lain.

## Temuan yang memerlukan follow-up

| Temuan | Risiko | Rekomendasi |
|---|---|---|
| Search bar masih placeholder | User mengira search aktif | Buat task command palette/search atau ubah copy menjadi “segera hadir” |
| Help button belum punya aksi | UX dead control | Hubungkan ke docs/help route atau sembunyikan sampai siap |
| Dashboard masih punya data statis | Bisa memberi kesan metrik real-time padahal naratif | Tandai sebagai curated status atau ganti ke read model pada task dashboard polish |
| Sidebar permission filtering terjadi di client | Bukan security issue jika route backend aman, tapi perlu dipahami | Dokumentasikan bahwa backend policy tetap wajib |

## Evidence

```bash
php artisan test --filter=Dashboard
npm run typecheck
npm run build
git diff --check
```

## Kesimpulan

Task 01 selesai sebagai baseline. Console shell sudah jelas menjadi base UI pattern: auth entry point, shared props, sidebar module registry, header command bar, theme behavior, dan dashboard landing sudah tertelusur. Tidak ada perubahan runtime pada task ini.

