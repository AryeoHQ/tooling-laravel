<?php

declare(strict_types=1);

namespace Tooling;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Tooling\Console\Commands\ToolingDiscover;
use Tooling\Console\Commands\ToolingOptimize;
use Tooling\Filesystem\Testing\Mixins\ProvidesFaking;
use Tooling\GeneratorCommands\MakeTestClass;

class Provider extends ServiceProvider
{
    protected false|string $configPath {
        get => $this->configPath ??= realpath(__DIR__.'/../../config/tooling.php');
    }

    public function register(): void
    {
        when($this->configPath, fn (string $path): null => $this->mergeConfigFrom($path, 'tooling'));

        Filesystem::mixin(new ProvidesFaking);

        $this->app->register(Composer\Provider::class);
        $this->app->register(PhpStan\Provider::class);
        $this->app->register(Rector\Provider::class);
        $this->app->register(Pint\Provider::class);
    }

    public function boot(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $this->commands(
            ToolingDiscover::class,
            ToolingOptimize::class,
            MakeTestClass::class,
        );
    }
}
