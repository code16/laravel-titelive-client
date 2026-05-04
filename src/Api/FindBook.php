<?php

namespace Code16\LaravelTiteliveClient\Api;

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;

class FindBook extends CacheableAction
{
    protected string $gencod;

    public function find(string $gencod)
    {
        $this->gencod = $gencod;

        return $this->executeCacheable();
    }

    protected function execute()
    {
        return app(BookDirectoryClient::class)
            ->setParam(BookDirectoryClient::GENCOD, $this->gencod)
            ->doFind();
    }
}
