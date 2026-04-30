<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\PhpStan\Console\Inspectors;

#[AsCommand(name: 'tooling:phpstan:cache-clear', description: 'Clear PHPStan result cache', aliases: ['tooling:phpstan:flush'])]
#[VendorBinary(inspector: Inspectors\CacheClear::class, binary: 'phpstan', command: 'clear-result-cache')]
class CacheClear extends Command
{
    use HandledByVendorBinary;
}
