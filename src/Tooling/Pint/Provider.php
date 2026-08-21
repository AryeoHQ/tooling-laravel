<?php

declare(strict_types=1);

namespace Tooling\Pint;

use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootCommands();
    }

    private function bootCommands(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $this->commands(
            Console\Commands\CloneBaseCommand::class,
            Console\Commands\Pint::class,
        );
    }
}
