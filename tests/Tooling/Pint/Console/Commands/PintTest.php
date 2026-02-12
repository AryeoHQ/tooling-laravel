<?php

declare(strict_types=1);

namespace Tests\Tooling\Pint\Console\Commands;

use Tests\TestCase;
use Tests\Tooling\Console\Commands\Concerns\VendorBinaryCases;
use Tests\Tooling\Console\Commands\Contracts\ForVendorBinary;
use Tooling\Pint;

class PintTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryCases;

    public string $command { get => Pint\Console\Commands\Pint::class; }

    public string $binary { get => 'pint'; }

    public null|string $subcommand { get => null; }

    public string $inspector { get => Pint\Console\Inspector::class; }
}
