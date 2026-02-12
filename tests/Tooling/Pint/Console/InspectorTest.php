<?php

declare(strict_types=1);

namespace Tests\Tooling\Pint\Console;

use Tests\TestCase;
use Tests\Tooling\Console\Inspectors\Concerns\InspectorCases;
use Tests\Tooling\Console\Inspectors\Contracts\ForInspector;
use Tooling\Pint;

class InspectorTest extends TestCase implements ForInspector
{
    use InspectorCases;

    public string $class { get => Pint\Console\Inspector::class; }

    public string $path { get => '/usr/local/bin/pint'; }

    public array $arguments {
        get => [
            ['name' => 'path', 'isArray' => true, 'configValue' => ['/custom/path']],
        ];
    }

    public array $options {
        get => [
            ['name' => 'preset', 'configValue' => 'laravel'],
        ];
    }
}
