<?php

namespace App\Modules\Console\BackupRestores\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Console\BackupRestores\Http\Requests\FullRestoreBackupRequest;
use App\Modules\Console\BackupRestores\Http\Requests\RestoreBackupRequest;
use App\Modules\Console\BackupRestores\Services\BackupRestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BackupRestoreController extends Controller
{
    public function __construct(
        private readonly BackupRestoreService $backups,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BackupRestoreService::class);

        return Inertia::render('console/backup-restore/index', [
            'overview' => $this->backups->overview(),
            'can' => [
                'export' => $request->user()?->can('backup-restore.export') ?? false,
                'restore' => $request->user()?->can('backup-restore.restore') ?? false,
                'fullExport' => $request->user()?->can('backup-restore.full-export') ?? false,
                'fullRestore' => $request->user()?->can('backup-restore.full-restore') ?? false,
            ],
        ]);
    }

    public function export(): JsonResponse
    {
        $this->authorize('export', BackupRestoreService::class);

        $fileName = 'settings-backup-'.now()->format('Ymd-His').'-'.Str::slug(config('app.name')).'.json';

        return response()
            ->json($this->backups->export(), 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
    }

    public function restore(RestoreBackupRequest $request): RedirectResponse
    {
        $summary = $this->backups->restore(
            $request->file('backup'),
            $request->boolean('restore_system_settings'),
            $request->boolean('restore_notification_templates'),
        );

        return back()->with(
            'success',
            "Restore selesai. {$summary['system_settings']} system setting dan {$summary['notification_templates']} notification template diproses.",
        );
    }

    public function fullExport()
    {
        $this->authorize('fullExport', BackupRestoreService::class);

        $path = $this->backups->createFullBackupZip();
        $fileName = 'full-backup-'.now()->format('Ymd-His').'-'.Str::slug(config('app.name')).'.zip';

        return response()->download($path, $fileName)->deleteFileAfterSend();
    }

    public function fullRestore(FullRestoreBackupRequest $request): RedirectResponse
    {
        $restoreDatabase = $request->boolean('restore_database');
        $summary = $this->backups->restoreFullBackup(
            $request->file('backup'),
            $restoreDatabase,
            $request->boolean('restore_storage_public'),
            $request->boolean('dry_run'),
        );

        if ($request->boolean('dry_run')) {
            return back()->with('success', 'Dry-run full restore valid. Signature, checksum, manifest, dan archive safety lulus tanpa menulis database/storage.');
        }

        if ($restoreDatabase) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('status', 'Full database restore selesai. Silakan login ulang karena session database ikut diperbarui.');
        }

        return back()->with(
            'success',
            'Full restore selesai. Database: '.($summary['database_restored'] ? 'diproses' : 'dilewati').", storage files: {$summary['storage_files_restored']}.",
        );
    }
}
