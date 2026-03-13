<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

use Tooling\GeneratorCommands\References\Contracts\TestCompanion;

final class TestCasesTrait extends GenericTrait implements TestCompanion
{
    public TestClass|TestCasesTrait $test {
        get => $this;
    }
}
