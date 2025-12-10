<?php

declare(strict_types=1);

namespace Mcp;

use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\Facades\Mcp;
use Mcp\Servers\Development;
use Mcp\Servers\Registrar\Registrar;

class Provider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerMcp();
    }

    public function boot(): void
    {
        $this->bootMcp();
    }

    private function bootMcp(): void
    {
        Mcp::local('development', Development::class);
    }

    private function registerMcp(): void
    {
        $this->app->singleton(Registrar::class, fn (): Registrar => new Registrar);
    }
}
