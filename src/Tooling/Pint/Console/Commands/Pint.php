<?php

declare(strict_types=1);

namespace Tooling\Pint\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\Pint\Console\Inspector;

#[AsCommand(name: 'tooling:pint', description: 'Run Laravel Pint code style fixer')]
#[VendorBinary(inspector: Inspector::class, binary: 'pint')]
class Pint extends Command
{
    use HandledByVendorBinary;
}
