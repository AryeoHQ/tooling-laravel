<?php

declare(strict_types=1);

namespace Tooling;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use PHPStan\Command\AnalyseCommand;
use Rector\Console\Command\ProcessCommand;
use Rector\Console\ConsoleApplication;
use Rector\DependencyInjection\RectorContainerFactory;
use Rector\ValueObject\Bootstrap\BootstrapConfigs;
use ReflectionClass;
use Tooling\Composer\ClassMap\Cache;
use Tooling\Composer\ClassMap\Collectors\All;
use Tooling\Composer\ClassMap\Collectors\Untested;
use Tooling\Composer\ClassMap\Listeners\RebuildClassMapCache;
use Tooling\Composer\ClassMapSource;
use Tooling\Composer\Composer;
use Tooling\Composer\Manifest;
use Tooling\Console\Commands\ToolingDiscover;
use Tooling\Console\Commands\ToolingOptimize;
use Tooling\Filesystem\Testing\Mixins\ProvidesFaking;
use Tooling\GeneratorCommands\MakeTestClass;
use Tooling\Pint\Console\Commands\CloneBaseCommand;

class Provider extends ServiceProvider
{
    protected false|string $configPath {
        get => $this->configPath ??= realpath(__DIR__.'/../../config/tooling.php');
    }

    public function boot(): void
    {
        $this->bootCommands();
        $this->bootViews();
        $this->bootClassMapCacheListener();
    }

    public function register(): void
    {
        $this->mergeConfig();
        $this->registerBindings();
    }

    private function mergeConfig(): void
    {
        when($this->configPath, fn (string $path): null => $this->mergeConfigFrom($path, 'tooling'));
    }

    private function registerBindings(): void
    {
        $this->registerFilesystemFakeMacro();
        $this->registerBindingsForComposer();
        $this->registerBindingsForClassMapCache();
        $this->registerBindingsForPhpStan();
        $this->registerBindingsForRector();
    }

    private function registerFilesystemFakeMacro(): void
    {
        Filesystem::mixin(new ProvidesFaking);
    }

    private function registerBindingsForComposer(): void
    {
        app()->singleton(Composer::class);
        app()->singleton(ClassMapSource::class);
        app()->singleton(Manifest::class);
    }

    private function registerBindingsForClassMapCache(): void
    {
        app()->singleton(Cache::class);
        app()->tag([All::class, Untested::class], 'tooling.classmap.collectors');
    }

    private function registerBindingsForPhpStan(): void
    {
        tap(
            AnalyseCommand::class, // @phpstan-ignore phpstanApi.classConstant
            fn ($commandClass) => app()->when(PhpStan\Console\Inspector::class)->needs($commandClass)->give(
                fn () => with(
                    new ReflectionClass($commandClass),
                    fn (ReflectionClass $reflection) => tap(
                        $reflection->newInstanceArgs([[], microtime(true)]),
                        fn (AnalyseCommand $command) => $reflection->getMethod('configure')->invoke($command)
                    )
                )
            )
        );
    }

    private function registerBindingsForRector(): void
    {
        app()->when(Rector\Console\Inspector::class)->needs(ProcessCommand::class)->give(function () {
            $container = with(
                new RectorContainerFactory,
                fn (RectorContainerFactory $factory) => with(
                    new BootstrapConfigs(config('tooling.rector.cli.options.config') ?? base_path('rector.php'), []),
                    fn (BootstrapConfigs $configs) => $factory->createFromBootstrapConfigs($configs)
                )
            );

            return tap(
                $container->make(ProcessCommand::class),
                fn (ProcessCommand $command) => with(
                    $container->make(ConsoleApplication::class),
                    fn (ConsoleApplication $application) => with(
                        new ReflectionClass($application),
                        fn (ReflectionClass $reflection) => $reflection
                            ->getMethod('addCustomOptions')
                            ->invoke($application, $command->getDefinition())
                    )
                )
            );
        });
    }

    private function bootCommands(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $this->commands(
            ToolingDiscover::class,
            ToolingOptimize::class,
            CloneBaseCommand::class,
            MakeTestClass::class,
            PhpStan\Console\Commands\Make\MakeRule::class,
            PhpStan\Console\Commands\PhpStan::class,
            Rector\Console\Commands\Make\MakeRule::class,
            Rector\Console\Commands\Rector::class,
            Pint\Console\Commands\Pint::class
        );
    }

    private function bootViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views/rector/rules', 'tooling.rector.rules.samples');
    }

    private function bootClassMapCacheListener(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        Event::listen(CommandFinished::class, RebuildClassMapCache::class);
    }
}
