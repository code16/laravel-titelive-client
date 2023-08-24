<?php

namespace Code16\LaravelTiteliveClient\Tests\Utils;

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Book;
use Illuminate\Support\Collection;

class BookDirectoryFakeClient implements BookDirectoryClient
{
    protected array $params = [];

    public function setParam(string $param, $value): BookDirectoryClient
    {
        $this->params[$param] = $value;

        return $this;
    }

    public function doSearch(bool $groupEditions = false): Collection
    {
        return collect();
    }

    public function doFind(): ?Book
    {
        return null;
    }

    public function doListForAuthors(): Collection
    {
        return collect();
    }

    public function doListEditions(): Collection
    {
        return collect();
    }
}
