<?php

declare(strict_types=1);

namespace Tooling\Pint\Console\Commands;

use Tests\TestCase;
use Tooling\Console\Testing\Concerns\VendorBinaryTestCases;
use Tooling\Console\Testing\Contracts\ForVendorBinary;
use Tooling\Pint;

class PintTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryTestCases;

    public string $command = Pint\Console\Commands\Pint::class;

    public string $binary = 'pint';

    public null|string $subcommand = null;

    public string $inspector = Pint\Console\Inspector::class;
}
