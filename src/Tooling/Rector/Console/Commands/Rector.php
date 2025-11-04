<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\Rector\Console\Inspector;

#[AsCommand(name: 'tooling:rector', description: 'Run Rector static analysis and refactoring')]
#[VendorBinary(inspector: Inspector::class, binary: 'rector', command: 'process')]
class Rector extends Command
{
    use HandledByVendorBinary;
}
