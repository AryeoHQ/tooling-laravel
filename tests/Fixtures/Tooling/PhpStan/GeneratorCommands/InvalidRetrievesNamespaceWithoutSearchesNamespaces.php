<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Illuminate\Console\GeneratorCommand;
use Tooling\GeneratorCommands\Concerns\RetrievesNamespaceFromInput;

class InvalidRetrievesNamespaceWithoutSearchesNamespaces extends GeneratorCommand
{
    use RetrievesNamespaceFromInput;

    protected $name = 'test:invalid-namespace';

    protected function getStub(): string
    {
        return '';
    }
}
