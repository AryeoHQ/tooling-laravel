<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands;

use Tests\TestCase;
use Tooling\Console\Testing\Concerns\VendorBinaryTestCases;
use Tooling\Console\Testing\Contracts\ForVendorBinary;
use Tooling\PhpStan;

class BisectTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryTestCases;

    public string $command = PhpStan\Console\Commands\Bisect::class;

    public string $binary = 'phpstan';

    public null|string $subcommand = 'bisect';

    public string $inspector = PhpStan\Console\Inspectors\Bisect::class;
}
