<?php

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryMockClientForDev;
use Code16\LaravelTiteliveClient\Api\FindBook;

beforeEach(function () {
    $this->fakeClient = new BookDirectoryMockClientForDev;
    $this->app->bind(BookDirectoryClient::class, fn () => $this->fakeClient);
});

it('finds a book', function () {
    app(FindBook::class)->find('123');

    expect($this->fakeClient->getParams())->toBe([BookDirectoryClient::GENCOD => '123']);
});
