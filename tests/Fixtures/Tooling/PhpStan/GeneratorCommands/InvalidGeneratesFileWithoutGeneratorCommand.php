<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\Contracts\Reference;

class InvalidGeneratesFileWithoutGeneratorCommand implements GeneratesFile
{
    public string $stub = '';

    public Stringable $nameInput {
        get => str('');
    }

    public Reference $reference {
        get => $this->createStub(Reference::class);
    }
}
