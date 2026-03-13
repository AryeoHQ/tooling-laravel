<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References\Concerns;

use Illuminate\Support\Stringable;

trait ManagesNamespace
{
    final public Stringable $baseNamespace {
        set(Stringable|string $value) {
            $this->baseNamespace = str($value)->start('\\')->rtrim('\\');
        }
    }
}
