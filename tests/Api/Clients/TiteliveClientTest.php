<?php

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Api\Clients\TiteLive\TiteLiveClient;
use Code16\LaravelTiteliveClient\Book;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->doSearchFixture = include __DIR__.'/../../fixtures/fixture_doSearch.php';
    $this->doFindFixture = include __DIR__.'/../../fixtures/fixture_doFind.php';

    Http::fake([
        'find.example/*' => Http::response($this->doFindFixture),
        'list.example/*' => Http::response($this->doSearchFixture),
        'search.example/*' => Http::response($this->doSearchFixture),
        'login.example/*' => Http::response(['token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWQiOjgwMSwiaWF0IjoxNTE2MjM5MDIyfQ.KLNk_-7Dq8g9nUz_WRQr72Bc0iz9xMXJ165r82A83v4'],200, ['Set-Cookie' => "refresh-token=123456789; domain=."]),
    ]);

    $this->withoutExceptionHandling();
});

it('finds a book', function () {
    $client = new TiteLiveClient('find.example', 'login.example', 'login@example.org', 'pwd');

    $result = $client
        ->setParam(BookDirectoryClient::GENCOD, '123')
        ->doFind();

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('User-Agent', 'qdb/v1.0')
            && str_contains($request->url(), '123')
            && $request['stocks'] == 1
            && $request['detail'] == 1
            && $request['base'] == 'paper';
    });

    expect($result)
        ->toBeInstanceOf(Book::class)
        ->and($result->price)->toEqual($this->doFindFixture['oeuvre']['article'][0]['prix'] * 100);
});

it('lists books of a category', function () {
    $client = new TiteLiveClient('list.example', 'login.example', 'login@example.org', 'pwd');

    $searchResults = $client
        ->setParam(BookDirectoryClient::SEARCH_AVAILABILITY, 'all')
        ->setParam(BookDirectoryClient::SEARCH_PAGE, 1)
        ->setParam(BookDirectoryClient::SEARCH_TOTAL_COUNT, 10)
        ->setParam(BookDirectoryClient::CATEGORY_CODES, '123')
        ->doSearch(true);

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('User-Agent', 'qdb/v1.0')
            && $request['detail'] == 0
            && $request['tri'] == ''
            && $request['codegtl'] == '12300000'
            && $request['stocks'] == 1
            && $request['base'] == 'paper';
    });

    expect($searchResults)
        ->toBeInstanceOf(Collection::class)
        ->and($searchResults->count())->toEqual(count($this->doSearchFixture['result']));
});

it('searches for books grouped by edition', function () {
    $client = new TiteLiveClient('search.example', 'login.example', 'login@example.org', 'pwd');

    $searchResults = $client
        ->setParam(BookDirectoryClient::SEARCH_AVAILABILITY, 'all')
        ->setParam(BookDirectoryClient::SEARCH_PAGE, 1)
        ->setParam(BookDirectoryClient::SEARCH_TOTAL_COUNT, 10)
        ->setParam(BookDirectoryClient::SEARCH_QUERY, 'my search')
        ->doSearch(true);

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('User-Agent', 'qdb/v1.0')
            && $request['detail'] == 0
            && $request['tri'] == ''
            && $request['mots'] == 'my search'
            && $request['stocks'] == 1
            && $request['base'] == 'paper';
    });

    expect($searchResults)
        ->toBeInstanceOf(Collection::class)
        ->and($searchResults->count())->toEqual(count($this->doSearchFixture['result']));

    // Check that the first book has all its editions gencod in ->editions
    $this->assertEqualsCanonicalizing(
        collect($this->doSearchFixture['result'][0]['article'])
            ->filter(fn ($edition) => in_array($edition['codesupport'], ['T', 'P'])
                && $edition['gencod'] != $this->doSearchFixture['result'][0]['gencod']
            )
            ->pluck('gencod')
            ->values()
            ->toArray(),
        $searchResults[0]->editions
    );
});

it('searches for books NOT grouped by edition', function () {
    $client = new TiteLiveClient('search.example', 'login.example', 'login@example.org', 'pwd');

    $searchResults = $client
        ->setParam(BookDirectoryClient::SEARCH_AVAILABILITY, 'all')
        ->setParam(BookDirectoryClient::SEARCH_PAGE, 1)
        ->setParam(BookDirectoryClient::SEARCH_TOTAL_COUNT, 100)
        ->setParam(BookDirectoryClient::SEARCH_QUERY, 'my search')
        ->doSearch();

    $editionCount = collect($this->doSearchFixture['result'])
        ->sum(function ($book) {
            return collect($book['article'])
                ->filter(function ($edition) {
                    return in_array($edition['codesupport'], ['T', 'P']);
                })
                ->count();
        });

    expect($searchResults)
        ->toBeInstanceOf(Collection::class)
        ->and($searchResults->count())->toEqual($editionCount);

    collect($this->doSearchFixture['result'][0]['article'])
        ->filter(fn ($edition) => in_array($edition['codesupport'], ['T', 'P']))
        ->pluck('gencod')
        ->values()
        ->each(fn ($gencod, $index) => expect($searchResults[$index]->id)->toEqual($gencod));
});

it('removes diacritics and accents from the search query', function () {
    $client = new TiteLiveClient('search.example', 'login.example', 'login@example.org', 'pwd');

    $client
        ->setParam(BookDirectoryClient::SEARCH_AVAILABILITY, 'all')
        ->setParam(BookDirectoryClient::SEARCH_PAGE, 1)
        ->setParam(BookDirectoryClient::SEARCH_TOTAL_COUNT, 10)
        ->setParam(BookDirectoryClient::SEARCH_QUERY, 'àéçè/ô-ûî+öüï')
        ->doSearch();

    Http::assertSent(function (Request $request) {
        if(str_contains($request->url(), 'search.example')) {
            return $request['mots'] == 'aece o ui oui';
        } else {
            return str_contains($request->url(), 'login.example');
        }
    });
});
