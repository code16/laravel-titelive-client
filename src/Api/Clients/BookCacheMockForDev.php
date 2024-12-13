<?php

namespace Code16\LaravelTiteliveClient\Api\Clients;

use Code16\LaravelTiteliveClient\Models\Book;

class BookCacheMockForDev implements BookCache
{
    public function force(bool $force): BookCache
    {
        return $this;
    }

    public function refreshIfNeeded(Book $book): Book
    {
        return $book;
    }
}
