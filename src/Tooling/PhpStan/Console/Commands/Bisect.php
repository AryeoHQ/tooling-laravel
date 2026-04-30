<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\PhpStan\Console\Inspectors;

#[AsCommand(name: 'tooling:phpstan:bisect', description: 'Bisect PHPStan releases to find a regression')]
#[VendorBinary(inspector: Inspectors\Bisect::class, binary: 'phpstan', command: 'bisect')]
class Bisect extends Command
{
    use HandledByVendorBinary;
}
