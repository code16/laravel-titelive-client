<?php

namespace Code16\LaravelTiteliveClient\Api\Clients;

use Code16\LaravelTiteliveClient\Concerns\UsesBookModel;
use Code16\LaravelTiteliveClient\Models\Book;
use Illuminate\Support\Collection;

class BookDirectoryMockClientForDev implements BookDirectoryClient
{
    use UsesBookModel;

    private array $params = [];

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParam(string $param, $value): BookDirectoryClient
    {
        $this->params[$param] = $value;

        return $this;
    }

    public function doSearch(bool $groupEditions = false): Collection
    {
        return collect(
            Book::factory()
                ->count($this->params[static::SEARCH_TOTAL_COUNT])
                ->make()
        );
    }

    public function doFind(): ?Book
    {
        if ($this->params[static::GENCOD] === '404') {
            return null;
        }

        return Book::factory([
            'id' => $this->params[static::GENCOD],
            'category_codes' => collect(range(0, 2))
                ->map(fn () => str_pad(rand(1000, 9999), 8, '0'))
                ->toArray(),
        ])->make();
    }

    public function doListForAuthors(): Collection
    {
        return collect(
            Book::factory()
                ->count(rand(2, 8))
                ->make()
        );
    }

    public function doListEditions(): Collection
    {
        return collect(
            Book::factory()
                ->count(rand(0, 3))
                ->make()
        );
    }
}
