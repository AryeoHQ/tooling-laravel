<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Console\Commands;

use Tests\TestCase;
use Tests\Tooling\Console\Commands\Concerns\VendorBinaryCases;
use Tests\Tooling\Console\Commands\Contracts\ForVendorBinary;
use Tooling\Rector;

class RectorTest extends TestCase implements ForVendorBinary
{
    use VendorBinaryCases;

    public string $command = Rector\Console\Commands\Rector::class;

    public string $binary = 'rector';

    public null|string $subcommand = 'process';

    public string $inspector = Rector\Console\Inspector::class;
}
