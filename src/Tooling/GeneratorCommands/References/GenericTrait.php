<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

use Illuminate\Support\Stringable;

class GenericTrait extends Reference
{
    public null|Stringable $subNamespace {
        get => null;
    }

    public TestClass|TestCasesTrait $test {
        get => new TestCasesTrait(
            name: $this->name->append('TestCases'),
            baseNamespace: $this->namespace,
        );
    }
}
