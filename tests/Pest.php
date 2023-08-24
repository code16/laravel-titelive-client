<?php

use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function fakeBookDirectory(BookDirectoryClient $fakeImplementation): TestCase
{
    app()->bind(BookDirectoryClient::class, fn () => $fakeImplementation);

    return test();
}
