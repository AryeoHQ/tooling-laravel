<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Commands\Make\References;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\References\GenericClass;

final class RectorRule extends GenericClass
{
    public null|Stringable $subNamespace {
        get => str('Rector\\Rules');
    }

    public Stringable $stubPath {
        get => str(__DIR__.'/stubs/rule.stub');
    }
}
