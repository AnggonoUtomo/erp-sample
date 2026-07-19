<?php

namespace App\Modules\Console\GlobalSearches\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Console\GlobalSearches\DTO\SearchContext;
use App\Modules\Console\GlobalSearches\DTO\SearchQuery;
use App\Modules\Console\GlobalSearches\Http\Requests\GlobalSearchRequest;
use App\Modules\Console\GlobalSearches\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;

class GlobalSearchController extends Controller
{
    public function __construct(
        private readonly GlobalSearchService $search,
    ) {}

    public function index(GlobalSearchRequest $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $results = $this->search->search(
            SearchQuery::fromRequest($request),
            SearchContext::fromUser($user),
        );

        return response()->json([
            'data' => collect($results)
                ->map(fn ($result) => $result->toArray())
                ->values(),
        ]);
    }
}
