<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\PHPStan\Console\Inspector;

#[AsCommand(name: 'tooling:phpstan', description: 'Run PHPStan static analysis')]
#[VendorBinary(inspector: Inspector::class, binary: 'phpstan', command: 'analyse')]
class PHPStan extends Command
{
    use HandledByVendorBinary;
}
