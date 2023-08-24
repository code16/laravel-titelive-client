<?php

namespace Code16\LaravelTiteliveClient\Api;

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Illuminate\Support\Collection;

class ListBooks extends CacheableAction
{
    protected bool $withUnavailable = true;
    protected string $categoryCode;
    protected int $page;
    protected int $count;

    public function withUnavailable(bool $withUnavailable = true): self
    {
        $this->withUnavailable = $withUnavailable;

        return $this;
    }

    public function listBooks(string $categoryCode, int $page = 1, int $count = 24): Collection
    {
        $this->categoryCode = $categoryCode;
        $this->page = $page;
        $this->count = $count;

        return $this->executeCacheable();
    }

    protected function execute()
    {
        return app(BookDirectoryClient::class)
            ->setParam(BookDirectoryClient::SEARCH_AVAILABILITY, $this->withUnavailable ? 'all' : 'available')
            ->setParam(BookDirectoryClient::SEARCH_PAGE, $this->page)
            ->setParam(BookDirectoryClient::SEARCH_TOTAL_COUNT, $this->count)
            ->setParam(BookDirectoryClient::CATEGORY_CODES, $this->categoryCode)
            ->doSearch(groupEditions: true);
    }
}
