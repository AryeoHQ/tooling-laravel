<?php

declare(strict_types=1);

namespace Tests\Tooling\Console\Commands\Contracts;

interface ForVendorBinary
{
    /** @var class-string<\Illuminate\Console\Command> */
    public string $command { get; }

    public string $binary { get; }

    public null|string $subcommand { get; }

    /** @var class-string<\Tooling\Console\Inspectors\Inspector> */
    public string $inspector { get; }
}
