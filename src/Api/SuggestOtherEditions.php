<?php

namespace Code16\LaravelTiteliveClient\Api;

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Models\Book;
use Illuminate\Support\Collection;

class SuggestOtherEditions extends CacheableAction
{
    protected string $bookTitle;

    protected array $authors;

    protected ?string $excludedGencod;

    public function suggestBooks(string $bookTitle, array $authors, ?string $excludedGencod = null): Collection
    {
        $this->bookTitle = $bookTitle;
        $this->authors = $authors;
        $this->excludedGencod = $excludedGencod;

        return $this->executeCacheable();
    }

    protected function execute()
    {
        return app(BookDirectoryClient::class)
            ->setParam(BookDirectoryClient::LIST_FOR_AUTHORS, implode(' ', $this->authors))
            ->setParam(BookDirectoryClient::SEARCH_AVAILABILITY, 'available')
            ->setParam(BookDirectoryClient::SEARCH_QUERY, $this->bookTitle)
            ->setParam(BookDirectoryClient::SEARCH_TOTAL_COUNT, 12)
            ->doListEditions()
            ->when($this->excludedGencod, function ($collection, $excludedGencod) {
                return $collection
                    ->filter(fn (Book $book) => $book->id != $excludedGencod);
            });
    }
}
