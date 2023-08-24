<?php

namespace Code16\LaravelTiteliveClient\Commands;

use Illuminate\Console\Command;

class LaravelTiteliveClientCommand extends Command
{
    public $signature = 'laravel-titelive-client';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
