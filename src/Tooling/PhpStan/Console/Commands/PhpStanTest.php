<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands;

use Tests\TestCase;
use Tooling\Console\Testing\Concerns\VendorBinaryTestCases;
use Tooling\Console\Testing\Contracts\ForVendorBinary;
use Tooling\PhpStan;

class PhpStanTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryTestCases;

    public string $command = PhpStan\Console\Commands\PhpStan::class;

    public string $binary = 'phpstan';

    public null|string $subcommand = 'analyse';

    public string $inspector = PhpStan\Console\Inspector::class;
}
