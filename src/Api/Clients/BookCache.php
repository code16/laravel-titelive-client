<?php

namespace Code16\LaravelTiteliveClient\Api\Clients;

use Code16\LaravelTiteliveClient\Book;

interface BookCache
{
    public function force(bool $force): self;

    public function refreshIfNeeded(Book $book): ?Book;
}
