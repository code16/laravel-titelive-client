<?php

namespace Code16\LaravelTiteliveClient\Api\Clients\TiteLive;

use Cache;
use Carbon\Carbon;
use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Book;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Transliterator;

class TiteLiveClient implements BookDirectoryClient
{
    protected string $endpoint;

    protected string $login_endpoint;

    protected string $login;

    protected string $password;

    protected array $params = [];

    public function __construct(string $endpoint, string $login_endpoint, string $login, string $password)
    {
        $this->endpoint = $endpoint;
        $this->login_endpoint = $login_endpoint;
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

    public function getParam(string $param): mixed
    {
        if ($label = $this->getLabelForParam($param)) {
            return $this->params[$label];
        }

        return $this->$param ?? null;
    }

    public function doSearch(bool $groupEditions = false): Collection
    {
        $this->params['detail'] = 0;
        $this->params['tri'] = '';

        return collect($this->requestApi('search')['result'] ?? [])
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
        if (isset($this->params[$this->getLabelForParam(static::GENCOD)])) {
            $this->params['detail'] = 1;
            $gencod = $this->getParam(static::GENCOD);
            // For this endpoint, The gencode is passed to TiteLive in the endpoint,
            // so it must not be sent as a parameter of the request
            unset($this->params[$this->getLabelForParam(static::GENCOD)]);

            return $this->makeOneBookFromTiteLiveResult($this->requestApi('ean/'.$gencod)['oeuvre'] ?? []);
        }

        throw new TiteLiveBookNotFoundException('Missing gencod parameter');
    }

    public function doListForAuthors(): Collection
    {
        $this->params['detail'] = 0;
        $this->params['tri'] = '';

        return collect($this->requestApi('search')['result'])
            ->map(function ($result) {
                return $this->makeOneBookFromTiteLiveResult($result);
            })
            ->filter();
    }

    public function doListEditions(): Collection
    {
        $this->params['detail'] = 0;
        $this->params['tri'] = '';

        return collect($this->requestApi('search')['result'])
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

    private function requestApi(string $endpoint, $retries = 0): array
    {
        try {
            $response = Http::retry(
                times: config('titelive-client.book_directory.api.retry.times'),
                sleepMilliseconds: config('titelive-client.book_directory.api.retry.sleep_milliseconds'),
            )
                ->withHeaders([
                    'User-Agent' => 'qdb/v1.0',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate, br',
                ])
                ->withToken($this->getAuthToken())
                ->get($this->buildEndpointUrl($endpoint, null, $this->buildParamsForRequest()))
                ->throw();
        } catch (\Exception $e) {
            report($e);

            if ($e instanceof RequestException) {
                Log::error($e->response->getBody());
                $error = $e?->response->json();
                if ($error['type'] === 'urn:epagine:GEN-404') {
                    throw new TiteLiveBookNotFoundException($error['ean'] ?? '');
                }
                throw new TiteLiveBookNotFoundException('Erreur : '.($error['title'] ?? '').' ('.($error['detail'] ?? ')'));
            }

            if ($e instanceof TiteLiveApiCredentialsException) {
                // starts by invalidating the token, then retry full login process
                match (true) {
                    $retries == 0 => Cache::forget('titelive_auth_token'),
                    $retries > 0 => Cache::forget('titelive_refresh_cookie'),
                };

                if ($retries < 2) {
                    return $this->requestApi($endpoint, $retries + 1);
                }
            }

            throw new TiteLiveBookNotFoundException('Unable to fetch data from titelive apis');
        }

        return $response->json() ?? [];
    }

    private function buildParamsForRequest(): string
    {
        $this->params['stocks'] = 1;
        $this->params['base'] = 'paper';

        return '?'.http_build_query($this->params);
    }

    private function buildEndpointUrl(string $endpoint, ?string $customBase = null, ?string $query = null): string
    {
        $base = str_ends_with($customBase ?? $this->endpoint, '/') ? ($customBase ?? $this->endpoint) : ($customBase ?? $this->endpoint).'/';
        $endpoint = str_starts_with($endpoint, '/') ? substr($endpoint, 1) : $endpoint;

        return $base.$endpoint.$query;
    }

    private function login(): array
    {
        try {
            return Cache::remember('titelive_refresh_cookie', 60 * 60 * 24, function () {
                $res = Http::throw()->asJson()->post($this->buildEndpointUrl('login/'.$this->login.'/token', $this->login_endpoint), [
                    'password' => $this->password,
                ]);

                return [
                    'cookie' => $res->cookies()->getCookieByName('refresh-token') ?? '',
                    'token' => json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $res->json('token') ?? '{}')[1])))),
                ];
            });
        } catch (\Exception $e) {
            report($e);
            throw new TiteLiveApiCredentialsException('Could not login to titelive apis');
        }
    }

    private function getAuthToken(): string
    {
        try {
            $params = $this->login();

            return Cache::remember('titelive_auth_token', 60 * 4, function () use ($params) {
                /** @var SetCookie $cookie */
                $cookie = $params['cookie'];

                $res = Http::throw()->asJson()->withCookies([
                    'refresh-token' => $cookie?->getValue(),
                ], $cookie?->getDomain())->get($this->buildEndpointUrl('login/'.$params['token']?->id.'/connexion/refresh', $this->login_endpoint));

                return $res->json('token');
            });
        } catch (\Exception $e) {
            report($e);
            if ($e instanceof RequestException) {
                Log::error($e->response?->getBody());
            }
            throw new TiteLiveApiCredentialsException('Could not get auth token');
        }
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
                return in_array($edition['codesupport'] ?? [], ['T', 'P', 'BL']);
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
                    return in_array($otherEdition['codesupport'] ?? [], ['T', 'P', 'BL'])
                        && $otherEdition['gencod'] != $edition['gencod'];
                })
                ->pluck('gencod')
                ->values()
                ->toArray(),
            'refreshed_at' => now()->toISOString(),
        ]);
    }
}
