<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\GeneratorCommands;

use Tooling\GeneratorCommands\Testing\Concerns\ReferenceTestCases;

class InvalidReferenceTestCasesWithoutTestsReference extends \Tests\TestCase
{
    use ReferenceTestCases;
}
