<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Console\Commands\Attributes\VendorBinary;
use Tooling\Console\Commands\Provides\HandledByVendorBinary;
use Tooling\Rector\Console\Inspectors;

#[AsCommand(name: 'tooling:rector:rules-list', description: 'Show loaded Rector rules')]
#[VendorBinary(inspector: Inspectors\RulesList::class, binary: 'rector', command: 'list-rules')]
class RulesList extends Command
{
    use HandledByVendorBinary;
}
