<?php

namespace Code16\LaravelTiteliveClient\Utils;

use Code16\LaravelTiteliveClient\Api\Clients\BookCache;
use Code16\LaravelTiteliveClient\Book;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;

/**
 * @property Collection|Book[] $books
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasBooksAttribute
{
    public function refreshBooks(bool $force = false): self
    {
        $this->update([
            'books' => $this->books
                ->map(function (Book $book) use ($force) {
                    return app(BookCache::class)
                        ->force($force)
                        ->refreshIfNeeded($book);
                })
                ->filter()
                ->values()
                ->toArray(),
        ]);

        return $this;
    }

    public function books(): Attribute
    {
        return Attribute::make(
            get: fn () => collect($this->fromJson($this->attributes['books'] ?? '[]'))
                ->whereNotNull()
                ->map(fn ($attributes) => new Book($attributes))
                ->values()
        );
    }
}
