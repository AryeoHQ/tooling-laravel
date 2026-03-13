<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\TestsReference;

use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\Testing\Contracts\TestsReference;

class InvalidWithoutReferenceTestCases extends \Tests\TestCase implements TestsReference
{
    public Reference $subject {
        get => $this->createStub(Reference::class);
    }

    public string $expectedName = 'Test';
}
