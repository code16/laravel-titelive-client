<?php

namespace Code16\LaravelTiteliveClient\Api;

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Book;
use Illuminate\Support\Collection;

class SuggestBooksFromAuthors extends CacheableAction
{
    protected array $authors;
    protected ?string $excludedGencod;

    public function suggestBooks(array $authors, string $excludedGencod = null): Collection
    {
        $this->authors = $authors;
        $this->excludedGencod = $excludedGencod;

        return $this->executeCacheable();
    }

    protected function execute()
    {
        return app(BookDirectoryClient::class)
            ->setParam(BookDirectoryClient::SEARCH_TOTAL_COUNT, 24)
            ->setParam(BookDirectoryClient::SEARCH_AVAILABILITY, 'available')
            ->setParam(BookDirectoryClient::LIST_FOR_AUTHORS, implode(' ', $this->authors))
            ->doListForAuthors()
            ->when($this->excludedGencod, function($collection, $excludedGencod) {
                return $collection
                    ->filter(fn (Book $book) => $book->id != $excludedGencod);
            });
    }
}
