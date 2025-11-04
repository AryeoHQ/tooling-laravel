<?php

declare(strict_types=1);

namespace Tooling;

use Illuminate\Support\ServiceProvider;
use PHPStan\Command\AnalyseCommand;
use Rector\Console\Command\ProcessCommand;
use Rector\Console\ConsoleApplication;
use Rector\DependencyInjection\RectorContainerFactory;
use Rector\ValueObject\Bootstrap\BootstrapConfigs;
use ReflectionClass;
use Tooling\Console\Commands\ToolingDiscover;
use Tooling\Pint\Console\Commands\CloneBaseCommand;

class Provider extends ServiceProvider
{
    protected false|string $configPath {
        get => $this->configPath ??= realpath(__DIR__.'/../../config/tooling.php');
    }

    public function boot(): void
    {
        $this->bootCommands();
    }

    public function register(): void
    {
        $this->mergeConfig();
        $this->registerBindings();
    }

    private function mergeConfig(): void
    {
        when($this->configPath, fn ($path) => $this->mergeConfigFrom($path, 'tooling'));
    }

    private function registerBindings(): void
    {
        $this->registerBindingsForPHPStan();
        $this->registerBindingsForRector();
    }

    private function registerBindingsForPHPStan(): void
    {
        tap(
            AnalyseCommand::class, // @phpstan-ignore phpstanApi.classConstant
            fn ($commandClass) => app()->when(PHPStan\Console\Inspector::class)->needs($commandClass)->give(
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
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands(
            ToolingDiscover::class,
            CloneBaseCommand::class,
            PHPStan\Console\Commands\PHPStan::class,
            Rector\Console\Commands\Rector::class,
            Pint\Console\Commands\Pint::class
        );
    }
}
