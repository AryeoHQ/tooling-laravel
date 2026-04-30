<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\Rector\Console\Inspectors;

#[AsCommand(name: 'tooling:rector:process', description: 'Run Rector refactoring', aliases: ['tooling:rector'])]
#[VendorBinary(inspector: Inspectors\Process::class, binary: 'rector', command: 'process')]
class Process extends Command
{
    use HandledByVendorBinary {
        handle as baseHandle;
        getOptions as baseGetOptions;
    }

    public function handle(): int
    {
        return $this->applyClearCache()->applyDryRun()->baseHandle();
    }

    private function applyClearCache(): static
    {
        $this->notForwardable->push('flush')->push('cache-clear');

        if ($this->option('flush') || $this->option('cache-clear')) {
            $this->input->setOption('clear-cache', true);
        }

        return $this;
    }

    private function applyDryRun(): static
    {
        $this->notForwardable->push('test');

        if ($this->option('test')) {
            $this->input->setOption('dry-run', true);
        }

        return $this;
    }

    protected function getOptions(): array
    {
        return [
            ...$this->baseGetOptions(),
            new InputOption('cache-clear', mode: InputOption::VALUE_NONE, description: 'Clear the cache before processing'),
            new InputOption('flush', mode: InputOption::VALUE_NONE, description: 'Alias for --cache-clear'),
            new InputOption('test', mode: InputOption::VALUE_NONE, description: 'Alias for --dry-run'),
        ];
    }
}
