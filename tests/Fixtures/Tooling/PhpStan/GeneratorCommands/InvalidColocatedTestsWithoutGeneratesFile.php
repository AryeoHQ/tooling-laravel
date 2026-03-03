<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Illuminate\Console\GeneratorCommand;
use Tooling\GeneratorCommands\Concerns\CreatesColocatedTests;

class InvalidColocatedTestsWithoutGeneratesFile extends GeneratorCommand
{
    use CreatesColocatedTests;

    protected $name = 'test:invalid-colocated';

    protected function getStub(): string
    {
        return '';
    }
}
