<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Contracts;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\References\Contracts\Reference;

interface GeneratesFile
{
    public string $stub { get; }

    public Stringable $nameInput { get; }

    public Reference $reference { get; }
}
