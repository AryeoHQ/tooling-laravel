<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\Contracts\Reference;

class InvalidGeneratesFileWithoutGeneratorCommand implements GeneratesFile
{
    public Stringable $nameInput {
        get => str('');
    }

    public Reference $reference {
        get => throw new \RuntimeException('Not implemented');
    }
}
