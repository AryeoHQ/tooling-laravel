<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\PhpStan\Console\Inspectors;

#[AsCommand(name: 'tooling:phpstan:diagnose', description: 'Run PHPStan diagnose')]
#[VendorBinary(inspector: Inspectors\Diagnose::class, binary: 'phpstan', command: 'diagnose')]
class Diagnose extends Command
{
    use HandledByVendorBinary;
}
