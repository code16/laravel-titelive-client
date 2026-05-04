<?php

namespace Code16\LaravelTiteliveClient\Concerns;

use Code16\LaravelTiteliveClient\Models\Book;

/**
 * @internal
 */
trait UsesBookModel
{
    /**
     * @return class-string<Book>
     */
    public static function bookModelClass(): string
    {
        return config('titelive-client.book_model_class');
    }
}
