<?php

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryMockClientForDev;
use Code16\LaravelTiteliveClient\Api\ListBooks;

beforeEach(function () {
    $this->fakeClient = new BookDirectoryMockClientForDev();
    $this->app->bind(BookDirectoryClient::class, fn () => $this->fakeClient);
});

it('lists books for a category', function () {
    app(ListBooks::class)->listBooks('CAT');

    expect($this->fakeClient->getParams())->toBe([
        BookDirectoryClient::SEARCH_AVAILABILITY => 'all',
        BookDirectoryClient::SEARCH_PAGE => 1,
        BookDirectoryClient::SEARCH_TOTAL_COUNT => 24,
        BookDirectoryClient::CATEGORY_CODES => 'CAT',
    ]);
});

it('lists only available books if needed', function () {
    app(ListBooks::class)->withUnavailable(false)->listBooks('CAT');

    expect($this->fakeClient->getParams()[BookDirectoryClient::SEARCH_AVAILABILITY])->toBe('available');
});
