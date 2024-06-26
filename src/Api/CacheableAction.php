<?php

namespace Code16\LaravelTiteliveClient\Api;

use Illuminate\Support\Facades\Cache;

abstract class CacheableAction
{
    private bool $withCache = true;

    public function withCache(bool $withCache = true): self
    {
        $this->withCache = $withCache;

        return $this;
    }

    protected function executeCacheable()
    {
        if (! $this->withCache || ! config('titelive-client.book_directory.use_cache')) {
            return $this->execute();
        }

        return Cache::remember(
            $this->generateUniqueCacheKey(),
            now()->addMinutes(config('titelive-client.book_directory.cache_duration')),
            fn () => $this->execute()
        );
    }

    abstract protected function execute();

    private function generateUniqueCacheKey(): string
    {
        return md5(
            static::class
            .'-'
            .collect(get_object_vars($this))
                ->map(fn ($value) => is_array($value) ? implode(',', $value) : (string) $value)
                ->implode('')
        );
    }
}
