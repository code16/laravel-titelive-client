<?php

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryMockClientForDev;
use Code16\LaravelTiteliveClient\Api\SearchBooks;

beforeEach(function () {
    $this->fakeClient = new BookDirectoryMockClientForDev;
    $this->app->bind(BookDirectoryClient::class, fn () => $this->fakeClient);
});

it('searches for books', function () {
    app(SearchBooks::class)->search('some query');

    expect($this->fakeClient->getParams())->toBe([
        BookDirectoryClient::SEARCH_AVAILABILITY => 'all',
        BookDirectoryClient::SEARCH_PAGE => 1,
        BookDirectoryClient::SEARCH_TOTAL_COUNT => 24,
        BookDirectoryClient::SEARCH_QUERY => 'some query',
    ]);
});

it('lists only available books if needed', function () {
    app(SearchBooks::class)->withUnavailable(false)->search('query');

    expect($this->fakeClient->getParams()[BookDirectoryClient::SEARCH_AVAILABILITY])->toBe('available');
});
