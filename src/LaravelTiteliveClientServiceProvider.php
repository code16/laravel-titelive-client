<?php

namespace Code16\LaravelTiteliveClient;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Code16\LaravelTiteliveClient\Commands\LaravelTiteliveClientCommand;

class LaravelTiteliveClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-titelive-client')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-titelive-client_table')
            ->hasCommand(LaravelTiteliveClientCommand::class);
    }
}
