<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Inspectors;

use Tests\TestCase;
use Tooling\Console\Testing\Attributes\DoesntExpectArguments;
use Tooling\Console\Testing\Concerns\InspectorTestCases;
use Tooling\Console\Testing\Contracts\ForInspector;
use Tooling\Rector;

#[DoesntExpectArguments]
class RulesListTest extends TestCase implements ForInspector
{
    use InspectorTestCases;

    public string $class = Rector\Console\Inspectors\RulesList::class;

    public string $path = '/usr/local/bin/rector';

    public array $arguments = [];

    public array $options {
        get => [
            ['name' => 'output-format', 'configValue' => 'console'],
            ['name' => 'only'],
        ];
    }
}
