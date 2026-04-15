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
        get => resolve(TestCasesTrait::class, [
            'name' => $this->name->append('TestCases'),
            'baseNamespace' => $this->namespace,
        ]);
    }

    public Stringable $stubPath {
        get => str(__DIR__.'/stubs/trait.stub');
    }
}
