<?php

namespace Code16\LaravelTiteliveClient\Api;

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Illuminate\Support\Collection;

class SearchBooks extends CacheableAction
{
    protected bool $withUnavailable = true;
    protected bool $groupByEdition = true;
    protected string $query;
    protected int $page;
    protected int $count;

    public function withUnavailable(bool $withUnavailable = true): self
    {
        $this->withUnavailable = $withUnavailable;

        return $this;
    }

    public function groupByEdition(bool $groupByEdition = true): self
    {
        $this->groupByEdition = $groupByEdition;

        return $this;
    }

    public function search(string $query, int $page = 1, int $count = 24): Collection
    {
        $this->query = $query;
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
            ->setParam(BookDirectoryClient::SEARCH_QUERY, $this->query)
            ->doSearch($this->groupByEdition);
    }
}
