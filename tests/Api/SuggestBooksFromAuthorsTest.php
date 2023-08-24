<?php

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryMockClientForDev;
use Code16\LaravelTiteliveClient\Api\SuggestBooksFromAuthors;
use Code16\LaravelTiteliveClient\Book;
use Illuminate\Support\Collection;

it('suggests books from authors', function () {
    $fakeClient = new BookDirectoryMockClientForDev();
    $this->app->bind(BookDirectoryClient::class, fn () => $fakeClient);

    app(SuggestBooksFromAuthors::class)->suggestBooks(['bob marley', 'doc gyneco']);

    expect($fakeClient->getParams())->toBe([
        BookDirectoryClient::SEARCH_TOTAL_COUNT => 24,
        BookDirectoryClient::SEARCH_AVAILABILITY => 'available',
        BookDirectoryClient::LIST_FOR_AUTHORS => 'bob marley doc gyneco',
    ]);
});

it('can exclude a gencod from suggestions', function () {
    $this->app->bind(BookDirectoryClient::class, fn () => new class extends BookDirectoryMockClientForDev
    {
        public function doListForAuthors(): Collection
        {
            return collect([
                Book::factory()->make(['id' => '123']),
                Book::factory()->make(['id' => '456']),
                Book::factory()->make(['id' => '789']),
                Book::factory()->make(['id' => '1234']),
            ]);
        }
    });

    expect(app(SuggestBooksFromAuthors::class)->suggestBooks(['bob marley'], '123'))
        ->toHaveCount(3);
});
