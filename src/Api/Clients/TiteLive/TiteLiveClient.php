<?php

namespace Code16\LaravelTiteliveClient\Api\Clients\TiteLive;

use Carbon\Carbon;
use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Book;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Transliterator;

class TiteLiveClient implements BookDirectoryClient
{
    protected string $endpoint;

    protected string $clientNumber;

    protected string $login;

    protected string $password;

    protected array $params = [];

    public function __construct(string $endpoint, string $clientNumber, string $login, string $password)
    {
        $this->endpoint = $endpoint;
        $this->clientNumber = $clientNumber;
        $this->login = $login;
        $this->password = $password;
    }

    public function setParam(string $param, $value): self
    {
        if ($label = $this->getLabelForParam($param)) {
            $this->params[$label] = $this->normalizeValue($value, $param);
        }

        return $this;
    }

    public function doSearch(bool $groupEditions = false): Collection
    {
        $this->params['detail'] = 0;
        $this->params['tri'] = '';

        return collect($this->requestApi('result'))
            ->map(function ($result) use ($groupEditions) {
                return $groupEditions
                    ? $this->makeOneBookFromTiteLiveResult($result)
                    : $this->makeAllEditionsFromTiteLiveResult($result);
            })
            ->flatten()
            ->take($this->params['nombre'])
            ->filter();
    }

    public function doFind(): ?Book
    {
        $this->params['detail'] = 1;

        return $this->makeOneBookFromTiteLiveResult($this->requestApi('oeuvre'));
    }

    public function doListForAuthors(): Collection
    {
        $this->params['detail'] = 0;
        $this->params['tri'] = '';

        return collect($this->requestApi('result'))
            ->map(function ($result) {
                return $this->makeOneBookFromTiteLiveResult($result);
            })
            ->filter();
    }

    public function doListEditions(): Collection
    {
        $this->params['detail'] = 0;
        $this->params['tri'] = '';

        return collect($this->requestApi('result'))
            ->map(function ($result) {
                return $this->makeAllEditionsFromTiteLiveResult($result);
            })
            ->flatten()
            ->take($this->params['nombre'])
            ->filter();
    }

    private function getLabelForParam(string $param): ?string
    {
        return [
            static::SEARCH_AVAILABILITY => 'dispo',
            static::SEARCH_PAGE => 'page',
            static::SEARCH_QUERY => 'mots',
            static::SEARCH_TOTAL_COUNT => 'nombre',
            static::LIST_FOR_AUTHORS => 'auteurs',
            static::GENCOD => 'ean',
            static::CATEGORY_CODES => 'codegtl',
        ][$param] ?? null;
    }

    private function requestApi(string $jsonName): array
    {
        $response = Http::retry(
            times: config('titelive-client.book_directory.api.retry.times'),
            sleepMilliseconds: config('titelive-client.book_directory.api.retry.sleep_milliseconds'),
        )
            ->withHeaders([
                'User-Agent' => 'qdb/v1.0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, br',
            ])
            ->get($this->endpoint, $this->buildParamsForRequest())
            ->throw()
            ->json();

        if (isset($response['Error'])) {
            throw new TiteLiveApiException($response['Error']);
        }

        return $response[$jsonName] ?? [];
    }

    private function buildParamsForRequest(): string
    {
        $this->params['mid'] = $this->clientNumber;
        $this->params['login'] = $this->login;
        $this->params['stocks'] = 1;
        $this->params['base'] = 'paper';
        ksort($this->params);

        // This nonsense if brought to you by TiteLive
        $hash = md5(
            collect($this->params)
                ->map(function ($value, $name) {
                    return "{$name}={$value}&";
                })
                ->values()
                ->implode('')
            .md5($this->password)
        );

        return http_build_query($this->params)."&hash={$hash}";
    }

    private function normalizeValue($value, string $param): string
    {
        if ($param === static::SEARCH_AVAILABILITY) {
            return $value === 'all' ? '0,1,2' : '1,2';
        }

        if ($param === static::SEARCH_QUERY) {
            // Remove accents and diacritics
            $value = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')
                ->transliterate($value);

            return preg_replace('/[\W]/', ' ', $value);
        }

        if ($param === static::CATEGORY_CODES) {
            return str_pad(trim($value), 8, '0', STR_PAD_RIGHT);
        }

        return (string) $value;
    }

    private function makeOneBookFromTiteLiveResult(array $result): ?Book
    {
        $edition = collect($result['article'] ?? [])
            ->when(isset($result['gencod']), function ($query) use ($result) {
                return $query->where('gencod', $result['gencod']);
            })
            ->first();

        if (! $edition) {
            return null;
        }

        return $this->mapBookFromApiResult($result, $edition);
    }

    private function makeAllEditionsFromTiteLiveResult(array $result): Collection
    {
        return collect($result['article'])
            ->filter(function ($edition) {
                return in_array($edition['codesupport'], ['T', 'P']);
            })
            ->map(function ($edition) use ($result) {
                return $this->mapBookFromApiResult($result, $edition);
            });
    }

    private function mapBookFromApiResult(array $book, array $edition): ?Book
    {
        if (! isset($edition['prix'])) {
            return null;
        }

        return new Book([
            'id' => $edition['gencod'],
            'title' => $book['titre'],
            'description' => $edition['resume'] ?? null,
            'authors' => collect(Arr::wrap($book['auteurs_multi']))->values()->toArray(),
            'category_codes' => collect($edition['gtl']['first'] ?? [])
                ->pluck('code')
                ->values()
                ->toArray(),
            'translator' => $edition['traducteur'] ?? null,
            'weight' => $edition['poids'] ?? null,
            'page_count' => $edition['pages'] ?? null,
            'readership' => $edition['lectorat'] ?? null,
            'editor' => $edition['editeur'] ?? null,
            'price' => round($edition['prix'] * 100),
            'published_date' => isset($edition['dateparution'])
                ? Carbon::createFromFormat('d/m/Y', $edition['dateparution'])
                : null,
            'support' => $edition['libellesupport'] ?? null,
            'visuals' => [
                'thumbnail' => $edition['imagesUrl']['vign'] ?? null,
                'large' => $edition['imagesUrl']['recto'] ?? null,
                'medium' => $edition['imagesUrl']['moyen'] ?? null,
            ],
            'availability' => $edition['dispo'] ?? 4,
            'stock' => $edition['stock'] ?? 0,
            'editions' => collect($book['article'] ?? [])
                ->filter(function ($otherEdition) use ($edition) {
                    return in_array($otherEdition['codesupport'], ['T', 'P'])
                        && $otherEdition['gencod'] != $edition['gencod'];
                })
                ->pluck('gencod')
                ->values()
                ->toArray(),
            'refreshed_at' => now()->toISOString(),
        ]);
    }
}
