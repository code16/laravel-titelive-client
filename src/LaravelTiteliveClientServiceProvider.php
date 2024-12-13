<?php

namespace Code16\LaravelTiteliveClient;

use Code16\LaravelTiteliveClient\Api\Clients\BookCache;
use Code16\LaravelTiteliveClient\Api\Clients\BookCacheMockForDev;
use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryClient;
use Code16\LaravelTiteliveClient\Api\Clients\BookDirectoryMockClientForDev;
use Code16\LaravelTiteliveClient\Api\Clients\RandomBasedOnRefreshDateBookCache;
use Code16\LaravelTiteliveClient\Api\Clients\TiteLive\TiteLiveClient;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelTiteliveClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-titelive-client')
            ->hasConfigFile();
    }

    public function packageRegistered()
    {
        $this->app->bind(BookDirectoryClient::class, function () {
            if (config('titelive-client.book_directory.mock', false)) {
                return new BookDirectoryMockClientForDev;
            }

            return new TiteLiveClient(
                config('titelive-client.book_directory.api.endpoint'),
                config('titelive-client.book_directory.api.login_endpoint'),
                config('titelive-client.book_directory.api.login'),
                config('titelive-client.book_directory.api.password'),
            );
        });

        $this->app->bind(BookCache::class, function ($app) {
            if (config('titelive-client.book_directory.mock', false)) {
                return new BookCacheMockForDev;
            }

            return $app->get(RandomBasedOnRefreshDateBookCache::class);
        });
    }
}
