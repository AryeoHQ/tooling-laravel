<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Commands;

use Tests\TestCase;
use Tooling\Console\Testing\Concerns\VendorBinaryTestCases;
use Tooling\Console\Testing\Contracts\ForVendorBinary;
use Tooling\Rector;

class RectorTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryTestCases;

    public string $command = Rector\Console\Commands\Rector::class;

    public string $binary = 'rector';

    public null|string $subcommand = 'process';

    public string $inspector = Rector\Console\Inspector::class;
}
