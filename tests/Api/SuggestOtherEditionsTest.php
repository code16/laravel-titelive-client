<?php

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryMockClientForDev;
use Code16\LaravelTiteliveClient\Api\SuggestOtherEditions;
use Code16\LaravelTiteliveClient\Book;
use Illuminate\Support\Collection;

beforeEach(function () {});

it('suggests other editions of a book', function () {
    $fakeClient = new BookDirectoryMockClientForDev;
    $this->app->bind(BookDirectoryClient::class, fn () => $fakeClient);

    app(SuggestOtherEditions::class)->suggestBooks('How to make $$', ['bob marley', 'doc gyneco']);

    expect($fakeClient->getParams())->toBe([
        BookDirectoryClient::LIST_FOR_AUTHORS => 'bob marley doc gyneco',
        BookDirectoryClient::SEARCH_AVAILABILITY => 'available',
        BookDirectoryClient::SEARCH_QUERY => 'How to make $$',
        BookDirectoryClient::SEARCH_TOTAL_COUNT => 12,
    ]);
});

it('can exclude a gencod from suggestions', function () {
    $this->app->bind(BookDirectoryClient::class, fn () => new class extends BookDirectoryMockClientForDev
    {
        public function doListEditions(): Collection
        {
            return collect([
                Book::factory()->make(['id' => '123']),
                Book::factory()->make(['id' => '456']),
                Book::factory()->make(['id' => '789']),
                Book::factory()->make(['id' => '1234']),
            ]);
        }
    });

    expect(app(SuggestOtherEditions::class)->suggestBooks('How to make $$', ['bob marley'], '123'))
        ->toHaveCount(3);
});
