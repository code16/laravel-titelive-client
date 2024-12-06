<?php

namespace Code16\LaravelTiteliveClient\Utils;

use Code16\LaravelTiteliveClient\Api\Clients\BookCache;
use Code16\LaravelTiteliveClient\Api\Clients\TiteLive\TiteLiveBookNotFoundException;
use Code16\LaravelTiteliveClient\Book;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasBookAttribute
{
    public function refreshBook(bool $force = false): self
    {
        try {
            $this->update([
                'book' => $refreshedBook = app(BookCache::class)
                    ->force($force)
                    ->refreshIfNeeded($this->book),
            ]);
        } catch(TiteLiveBookNotFoundException $e) {
            $this->delete();
        }

        return $this;
    }

    public function book(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['book'] ?? null
                ? new Book($this->fromJson($this->attributes['book']))
                : null
        );
    }
}
