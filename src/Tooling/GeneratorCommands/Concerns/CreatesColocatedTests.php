<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Symfony\Component\Console\Input\InputOption;
use Tooling\GeneratorCommands\MakeTestClass;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 * @mixin \Tooling\GeneratorCommands\Contracts\GeneratesFile
 */
trait CreatesColocatedTests
{
    use CreatesMatchingTest;

    protected function addTestOptions(): void
    {
        $this->getDefinition()->addOption(new InputOption(
            'test',
            't',
            InputOption::VALUE_NEGATABLE,
            "Create a co-located test for the {$this->type}",
            true,
        ));
    }

    protected function handleTestCreation($path): bool // @phpstan-ignore missingType.parameter
    {
        if (! $this->option('test')) {
            return false;
        }

        return $this->call(MakeTestClass::class, [
            'class' => $this->reference->fqcn->toString(),
            '--force' => $this->hasOption('force') && $this->option('force'),
        ]) === self::SUCCESS;
    }
}
