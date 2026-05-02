<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Inspectors;

use Tests\TestCase;
use Tooling\Console\Testing\Attributes\ExpectsArguments;
use Tooling\Console\Testing\Concerns\InspectorTestCases;
use Tooling\Console\Testing\Contracts\ForInspector;
use Tooling\Rector;

#[ExpectsArguments]
class ProcessTest extends TestCase implements ForInspector
{
    use InspectorTestCases;

    public string $class = Rector\Console\Inspectors\Process::class;

    public string $path = '/usr/local/bin/rector';

    public array $arguments {
        get => [
            ['name' => 'source', 'isArray' => true, 'configValue' => ['/custom/path']],
        ];
    }

    public array $options {
        get => [
            ['name' => 'config', 'configValue' => 'rector.php'],
            ['name' => 'dry-run'],
        ];
    }
}
