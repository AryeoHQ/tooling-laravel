<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\References\Contracts\TestCompanion;

final class TestCasesTrait extends GenericTrait implements TestCompanion
{
    public TestClass|TestCasesTrait $test {
        get => $this;
    }

    public Stringable $stubPath {
        get => str(__DIR__.'/stubs/cases.stub');
    }
}
