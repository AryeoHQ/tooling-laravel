<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Testing\Contracts;

use Tooling\GeneratorCommands\References\Contracts\Reference;

interface TestsReference
{
    public Reference $subject { get; }

    public string $expectedName { get; }
}
