<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Illuminate\Console\GeneratorCommand;
use Tooling\GeneratorCommands\Concerns\GeneratorCommandCompatibility;

class InvalidCompatibilityWithoutGeneratesFile extends GeneratorCommand
{
    use GeneratorCommandCompatibility;

    protected $name = 'test:invalid-compat';

    protected function getStub(): string
    {
        return '';
    }
}
