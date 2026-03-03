<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\Contracts\Reference;

class InvalidGeneratesFileWithoutCompatibility extends GeneratorCommand implements GeneratesFile
{
    protected $name = 'test:invalid';

    public string $stub = '';

    public Stringable $nameInput {
        get => str('');
    }

    public Reference $reference {
        get => $this->createStub(Reference::class);
    }
}
