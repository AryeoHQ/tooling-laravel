<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Commands\Make\References;

use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\Testing\Concerns\ReferenceTestCases;
use Tooling\GeneratorCommands\Testing\Contracts\TestsReference;

#[CoversClass(RectorRule::class)]
class RectorRuleTest extends TestCase implements TestsReference
{
    use ReferenceTestCases;

    public Reference $subject {
        get => new RectorRule(name: 'TestRule', baseNamespace: 'App');
    }

    public string $expectedName = 'TestRule';

    public null|string $expectedSubdirectory = null;
}
