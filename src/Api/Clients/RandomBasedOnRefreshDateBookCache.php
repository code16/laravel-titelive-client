<?php

namespace Code16\LaravelTiteliveClient\Api\Clients;

use Code16\LaravelTiteliveClient\Api\FindBook;
use Code16\LaravelTiteliveClient\Book;
use Carbon\Carbon;

/**
 * This cache strategy is based on a simple rule:
 * the older the refreshed_at date of the book, the more likely it is to be refreshed this time.
 */
class RandomBasedOnRefreshDateBookCache implements BookCache
{
    protected bool $force = false;
    protected FindBook $findBook;

    public function __construct(FindBook $findBook)
    {
        $this->findBook = $findBook;
    }

    public function force(bool $force): BookCache
    {
        $this->force = $force;

        return $this;
    }

    public function refreshIfNeeded(Book $book): ?Book
    {
        if($this->force || $this->shouldRefresh(new Carbon($book->refreshed_at))) {
            return $this->findBook->find($book->id);
        }

        return $book;
    }

    private function shouldRefresh(Carbon $refreshedAt): bool
    {
        $diff = $refreshedAt->diffInDays(now());

        if($diff <= 1) {
            return false;
        }

        if($diff <= 5) { // Between 1 and 5 days since last refresh, we got 10% chance of refresh
            return mt_rand(0, 99) < 10;
        }

        if($diff <= 15) {
            return mt_rand(0, 99) < 30;
        }

        if($diff <= 30) {
            return mt_rand(0, 99) < 50;
        }

        return mt_rand(0, 99) < 75;
    }
}
