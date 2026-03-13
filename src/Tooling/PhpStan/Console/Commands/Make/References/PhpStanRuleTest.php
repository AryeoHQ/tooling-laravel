<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Commands\Make\References;

use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\References\ReferenceTestCases;
use Tooling\GeneratorCommands\Testing\Contracts\TestsReference;

#[CoversClass(PhpStanRule::class)]
class PhpStanRuleTest extends TestCase implements TestsReference
{
    use ReferenceTestCases;

    public Reference $subject {
        get => new PhpStanRule(name: 'TestRule', baseNamespace: 'Workbench\\App');
    }

    public string $expectedName = 'TestRule';
}
