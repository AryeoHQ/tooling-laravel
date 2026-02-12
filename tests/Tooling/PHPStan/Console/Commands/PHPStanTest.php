<?php

declare(strict_types=1);

namespace Tests\Tooling\PHPStan\Console\Commands;

use Tests\TestCase;
use Tests\Tooling\Console\Commands\Concerns\VendorBinaryCases;
use Tests\Tooling\Console\Commands\Contracts\ForVendorBinary;
use Tooling\PHPStan;

class PHPStanTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryCases;

    public string $command { get => PHPStan\Console\Commands\PHPStan::class; }

    public string $binary { get => 'phpstan'; }

    public null|string $subcommand { get => 'analyse'; }

    public string $inspector { get => PHPStan\Console\Inspector::class; }
}
