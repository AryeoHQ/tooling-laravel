<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\PhpStan\Console\Inspectors;

#[AsCommand(name: 'tooling:phpstan:analyze', description: 'Run PHPStan static analysis', aliases: ['tooling:phpstan'])]
#[VendorBinary(inspector: Inspectors\Analyze::class, binary: 'phpstan', command: 'analyse')]
class Analyze extends Command
{
    use HandledByVendorBinary {
        handle as baseHandle;
        getOptions as baseGetOptions;
    }

    public function handle(): int
    {
        return $this->clearCacheIfRequested()->baseHandle();
    }

    private function clearCacheIfRequested(): static
    {
        $this->notForwardable->push('flush')->push('cache-clear');

        if ($this->option('flush') || $this->option('cache-clear')) {
            $this->call('tooling:phpstan:cache-clear', [
                '--configuration' => $this->option('configuration'),
            ]);
        }

        return $this;
    }

    protected function getOptions(): array
    {
        return [
            ...$this->baseGetOptions(),
            new InputOption('cache-clear', mode: InputOption::VALUE_NONE, description: 'Clear the result cache before analysing'),
            new InputOption('flush', mode: InputOption::VALUE_NONE, description: 'Alias for --cache-clear'),
        ];
    }
}
