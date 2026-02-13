<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Console\Commands;

use Tests\TestCase;
use Tests\Tooling\Console\Commands\Concerns\VendorBinaryCases;
use Tests\Tooling\Console\Commands\Contracts\ForVendorBinary;
use Tooling\PhpStan;

class PhpStanTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryCases;

    public string $command { get => PhpStan\Console\Commands\PhpStan::class; }

    public string $binary { get => 'phpstan'; }

    public null|string $subcommand { get => 'analyse'; }

    public string $inspector { get => PhpStan\Console\Inspector::class; }
}
