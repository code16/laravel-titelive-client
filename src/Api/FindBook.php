<?php

namespace Code16\LaravelTiteliveClient\Api;

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;

class FindBook
{
    public function find(string $gencod)
    {
        return app(BookDirectoryClient::class)
            ->setParam(BookDirectoryClient::GENCOD, $gencod)
            ->doFind();
    }
}
