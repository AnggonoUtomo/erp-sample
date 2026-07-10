<?php

namespace App\Modules\HR\JobLevels\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\JobLevels\Http\Requests\StoreJobLevelRequest;
use App\Modules\HR\JobLevels\Http\Requests\UpdateJobLevelRequest;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\JobLevels\Services\JobLevelsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class JobLevelsController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly JobLevelsService $jobLevels,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.JobLevel::class, only: ['index']),
            new Middleware('can:create,'.JobLevel::class, only: ['store']),
            new Middleware('can:update,jobLevel', only: ['update']),
            new Middleware('can:delete,jobLevel', only: ['destroy']),
            new Middleware('can:restore,jobLevel', only: ['restore']),
            new Middleware('can:forceDelete,jobLevel', only: ['forceDestroy']),
        ];
    }

    public function index(Request $request): Response
    {
        return Inertia::render('hr/job-levels/index', $this->jobLevels->getPageData($request->only(['search', 'status', 'archive', 'per_page'])));
    }

    public function store(StoreJobLevelRequest $request): RedirectResponse
    {
        $this->jobLevels->create($request->toDto());

        return back()->with('success', 'Job level berhasil dibuat.');
    }

    public function update(UpdateJobLevelRequest $request, JobLevel $jobLevel): RedirectResponse
    {
        $this->jobLevels->update($jobLevel, $request->toDto());

        return back()->with('success', 'Job level berhasil diperbarui.');
    }

    public function destroy(JobLevel $jobLevel): RedirectResponse
    {
        $this->jobLevels->delete($jobLevel);

        return back()->with('success', 'Job level dipindahkan ke arsip.');
    }

    public function restore(JobLevel $jobLevel): RedirectResponse
    {
        $this->jobLevels->restore($jobLevel);

        return back()->with('success', 'Job level berhasil dipulihkan.');
    }

    public function forceDestroy(JobLevel $jobLevel): RedirectResponse
    {
        $this->jobLevels->forceDelete($jobLevel);

        return back()->with('success', 'Job level berhasil dihapus permanen.');
    }
}
