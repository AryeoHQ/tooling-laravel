<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands\Make\References;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\References\GenericClass;

final class PhpStanRule extends GenericClass
{
    public null|Stringable $subNamespace {
        get => str('PhpStan\\Rules');
    }

    public Stringable $stubPath {
        get => str(__DIR__.'/stubs/rule.stub');
    }
}
