<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References\Contracts;

use Illuminate\Support\Stringable;

interface Reference
{
    public Stringable $name { get; }

    public Stringable $fqcn { get; }

    public Stringable $namespace { get; }

    public Stringable $directory { get; }

    public Stringable $filePath { get; }

    public Stringable $baseNamespace { get; }

    public null|Stringable $subNamespace { get; }

    public Stringable $stubPath { get; }
}
