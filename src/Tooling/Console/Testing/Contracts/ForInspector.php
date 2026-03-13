<?php

declare(strict_types=1);

namespace Tooling\Console\Testing\Contracts;

use Tooling\Console\Inspectors\Inspector;

interface ForInspector
{
    /** @var class-string<Inspector> */
    public string $class { get; }

    public string $path { get; }

    /** @var array<int, array{name: string, isArray?: bool, configValue?: mixed}> */
    public array $arguments { get; }

    /** @var array<int, array{name: string, configValue?: mixed}> */
    public array $options { get; }
}
