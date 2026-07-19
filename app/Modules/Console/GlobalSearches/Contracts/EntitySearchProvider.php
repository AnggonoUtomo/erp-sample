<?php

namespace App\Modules\Console\GlobalSearches\Contracts;

use App\Modules\Console\GlobalSearches\DTO\SearchContext;
use App\Modules\Console\GlobalSearches\DTO\SearchQuery;
use App\Modules\Console\GlobalSearches\DTO\SearchResult;

interface EntitySearchProvider
{
    public function key(): string;

    public function label(): string;

    public function canSearch(SearchContext $context): bool;

    /**
     * @return list<SearchResult>
     */
    public function search(SearchQuery $query, SearchContext $context): array;
}
