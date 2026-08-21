<?php

declare(strict_types=1);

namespace Tooling\Rector;

use Illuminate\Support\ServiceProvider;
use Rector\Console\Command\ListRulesCommand;
use Rector\Console\Command\ProcessCommand;
use Rector\Console\ConsoleApplication;
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
        $this->bootViews();
    }

    private function registerBindings(): void
    {
        app()->when(Console\Inspectors\Process::class)->needs(ProcessCommand::class)->give(
            fn () => $this->makeCommandWithoutContainer(ProcessCommand::class)
        );

        app()->when(Console\Inspectors\RulesList::class)->needs(ListRulesCommand::class)->give(
            fn () => $this->makeCommandWithoutContainer(ListRulesCommand::class)
        );
    }

    private function bootCommands(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $this->commands(
            Console\Commands\Make\MakeRule::class,
            Console\Commands\RulesList::class,
            Console\Commands\Process::class,
        );
    }

    private function bootViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../../resources/views/rector/rules', 'tooling.rector.rules.samples');
    }

    /**
     * Build a Rector command without the Rector container.
     *
     * Rector bundles an unprefixed copy of PHPStan\PhpDocParser. If we build the
     * Rector container, it loads that copy and causes a conflict with phpstan.phar.
     * Larastan constructs all artisan commands during analysis, so this method
     * builds the command through reflection to get the full option definition
     * without the container.
     *
     * @template TCommand of ProcessCommand|ListRulesCommand
     *
     * @param  class-string<TCommand>  $class
     * @return TCommand
     */
    private function makeCommandWithoutContainer(string $class): ProcessCommand|ListRulesCommand
    {
        // Rector prefixes its vendored Symfony — find the base class through the parent chain.
        $command = tap(
            (new ReflectionClass($class))->newInstanceWithoutConstructor(),
            fn ($command) => (new ReflectionClass(collect(class_parents($class))->last()))
                ->getConstructor()
                ->invoke($command)
        );

        with(
            new ReflectionClass(ConsoleApplication::class),
            fn (ReflectionClass $application) => $application->getMethod('addCustomOptions')->invoke(
                $application->newInstanceWithoutConstructor(),
                $command->getDefinition()
            )
        );

        return $command;
    }
}
