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

    public string $command = PhpStan\Console\Commands\PhpStan::class;

    public string $binary = 'phpstan';

    public null|string $subcommand = 'analyse';

    public string $inspector = PhpStan\Console\Inspector::class;
}
