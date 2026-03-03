<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\Testing\Concerns\ReferenceTestCases;
use Tooling\GeneratorCommands\Testing\Contracts\TestsReference;

class ValidReferenceTestCasesWithTestsReference extends \Tests\TestCase implements TestsReference
{
    use ReferenceTestCases;

    public Reference $subject {
        get => $this->createStub(Reference::class);
    }

    public string $expectedName = 'Test';

    public null|string $expectedSubdirectory = null;
}
