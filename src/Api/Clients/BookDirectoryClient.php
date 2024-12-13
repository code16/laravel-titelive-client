<?php

namespace Code16\LaravelTiteliveClient\Api\Clients;

use Code16\LaravelTiteliveClient\Models\Book;
use Illuminate\Support\Collection;

interface BookDirectoryClient
{
    const SEARCH_AVAILABILITY = 'search_availability';

    const SEARCH_PAGE = 'search_page';

    const SEARCH_TOTAL_COUNT = 'search_total_count';

    const SEARCH_QUERY = 'search_query';

    const LIST_FOR_AUTHORS = 'list_for_authors';

    const GENCOD = 'gencod';

    const CATEGORY_CODES = 'category_codes';

    public function setParam(string $param, $value): self;

    public function doSearch(bool $groupEditions = false): Collection;

    public function doListForAuthors(): Collection;

    public function doListEditions(): Collection;

    public function doFind(): ?Book;
}
