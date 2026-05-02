<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands;

use Tests\TestCase;
use Tooling\Console\Testing\Concerns\VendorBinaryTestCases;
use Tooling\Console\Testing\Contracts\ForVendorBinary;
use Tooling\PhpStan;

class CacheClearTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryTestCases;

    public string $command = PhpStan\Console\Commands\CacheClear::class;

    public string $binary = 'phpstan';

    public null|string $subcommand = 'clear-result-cache';

    public string $inspector = PhpStan\Console\Inspectors\CacheClear::class;
}
