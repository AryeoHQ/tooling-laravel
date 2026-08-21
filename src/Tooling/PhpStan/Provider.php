<?php

declare(strict_types=1);

namespace Tooling\PhpStan;

use Illuminate\Support\ServiceProvider;
use PHPStan\Command\AnalyseCommand;
use PHPStan\Command\BisectCommand;
use PHPStan\Command\ClearResultCacheCommand;
use PHPStan\Command\DiagnoseCommand;
use PHPStan\Command\DumpParametersCommand;
use ReflectionClass;

class Provider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBindings();
    }

    public function boot(): void
    {
        $this->bootCommands();
    }

    private function registerBindings(): void
    {
        tap(
            AnalyseCommand::class, // @phpstan-ignore phpstanApi.classConstant
            fn ($commandClass) => app()->when(Console\Inspectors\Analyze::class)->needs($commandClass)->give(
                fn () => with(
                    new ReflectionClass($commandClass),
                    fn (ReflectionClass $reflection) => tap(
                        $reflection->newInstanceArgs([[], microtime(true)]),
                        fn (AnalyseCommand $command) => $reflection->getMethod('configure')->invoke($command)
                    )
                )
            )
        );

        tap(
            ClearResultCacheCommand::class, // @phpstan-ignore phpstanApi.classConstant
            fn ($commandClass) => app()->when(Console\Inspectors\CacheClear::class)->needs($commandClass)->give(
                fn () => with(
                    new ReflectionClass($commandClass),
                    fn (ReflectionClass $reflection) => tap(
                        $reflection->newInstanceArgs([[]]),
                        fn (ClearResultCacheCommand $command) => $reflection->getMethod('configure')->invoke($command)
                    )
                )
            )
        );

        tap(
            DumpParametersCommand::class, // @phpstan-ignore phpstanApi.classConstant
            fn ($commandClass) => app()->when(Console\Inspectors\ParametersDump::class)->needs($commandClass)->give(
                fn () => with(
                    new ReflectionClass($commandClass),
                    fn (ReflectionClass $reflection) => tap(
                        $reflection->newInstanceArgs([[]]),
                        fn (DumpParametersCommand $command) => $reflection->getMethod('configure')->invoke($command)
                    )
                )
            )
        );

        tap(
            DiagnoseCommand::class, // @phpstan-ignore phpstanApi.classConstant
            fn ($commandClass) => app()->when(Console\Inspectors\Diagnose::class)->needs($commandClass)->give(
                fn () => with(
                    new ReflectionClass($commandClass),
                    fn (ReflectionClass $reflection) => tap(
                        $reflection->newInstanceArgs([[]]),
                        fn (DiagnoseCommand $command) => $reflection->getMethod('configure')->invoke($command)
                    )
                )
            )
        );

        tap(
            BisectCommand::class, // @phpstan-ignore phpstanApi.classConstant
            fn ($commandClass) => app()->when(Console\Inspectors\Bisect::class)->needs($commandClass)->give(
                fn () => with(
                    new ReflectionClass($commandClass),
                    fn (ReflectionClass $reflection) => tap(
                        $reflection->newInstance(),
                        fn (BisectCommand $command) => $reflection->getMethod('configure')->invoke($command)
                    )
                )
            )
        );
    }

    private function bootCommands(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $this->commands(
            Console\Commands\Make\MakeRule::class,
            Console\Commands\Analyze::class,
            Console\Commands\Bisect::class,
            Console\Commands\CacheClear::class,
            Console\Commands\Diagnose::class,
            Console\Commands\ParametersDump::class,
        );
    }
}
