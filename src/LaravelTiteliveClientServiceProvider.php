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
        $this->app->bind(BookDirectoryClient::class, function ($app) {
            if(config('laravel-titelive-client.book_directory.mock', false)) {
                return new BookDirectoryMockClientForDev();
            }

            return new TiteLiveClient(
                config('services.titelive.endpoint'),
                config('services.titelive.client_number'),
                config('services.titelive.login'),
                config('services.titelive.password'),
            );
        });

        $this->app->bind(BookCache::class, function ($app) {
            if(config('laravel-titelive-client.book_directory.mock', false)) {
                return new BookCacheMockForDev();
            }

            return $app->get(RandomBasedOnRefreshDateBookCache::class);
        });
    }
}
