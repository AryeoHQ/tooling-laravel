<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Illuminate\Console\GeneratorCommand;
use Tooling\GeneratorCommands\Concerns\SearchesClasses;

class InvalidSearchesClassesWithoutSearchesNamespaces extends GeneratorCommand
{
    use SearchesClasses;

    protected $name = 'test:invalid-searches';

    protected function getStub(): string
    {
        return '';
    }
}
