<?php

namespace Code16\LaravelTiteliveClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Code16\LaravelTiteliveClient\LaravelTiteliveClient
 */
class LaravelTiteliveClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Code16\LaravelTiteliveClient\LaravelTiteliveClient::class;
    }
}
