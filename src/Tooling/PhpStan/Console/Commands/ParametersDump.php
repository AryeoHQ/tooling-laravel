<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\PhpStan\Console\Inspectors;

#[AsCommand(name: 'tooling:phpstan:parameters-dump', description: 'Dump PHPStan parameters', aliases: ['tooling:phpstan:parameters'])]
#[VendorBinary(inspector: Inspectors\ParametersDump::class, binary: 'phpstan', command: 'dump-parameters')]
class ParametersDump extends Command
{
    use HandledByVendorBinary;
}
