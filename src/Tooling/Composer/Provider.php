<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Tooling\Composer\ClassMap\Cache;
use Tooling\Composer\ClassMap\Collectors\All;
use Tooling\Composer\ClassMap\Collectors\Untested;
use Tooling\Composer\ClassMap\Listeners\RebuildClassMapCache;

class Provider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBindings();
    }

    public function boot(): void
    {
        $this->bootListeners();
    }

    private function registerBindings(): void
    {
        app()->singleton(Composer::class);
        app()->singleton(ClassMapSource::class);
        app()->singleton(Manifest::class);
        app()->singleton(Cache::class);
        app()->tag([All::class, Untested::class], 'tooling.classmap.collectors');
    }

    private function bootListeners(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        Event::listen(CommandFinished::class, RebuildClassMapCache::class);
    }
}
