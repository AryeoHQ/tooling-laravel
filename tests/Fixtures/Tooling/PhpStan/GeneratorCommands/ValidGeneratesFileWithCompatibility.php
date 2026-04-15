<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\Concerns\GeneratorCommandCompatibility;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\Contracts\Reference;

class ValidGeneratesFileWithCompatibility extends GeneratorCommand implements GeneratesFile
{
    use GeneratorCommandCompatibility;

    protected $name = 'test:valid';

    public Stringable $nameInput {
        get => str('');
    }

    public Reference $reference {
        get => throw new \RuntimeException('Not implemented');
    }
}
