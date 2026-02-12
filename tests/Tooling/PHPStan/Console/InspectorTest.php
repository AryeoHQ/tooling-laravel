<?php

declare(strict_types=1);

namespace Tests\Tooling\PHPStan\Console;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Input\InputOption;
use Tests\TestCase;
use Tests\Tooling\Console\Inspectors\Concerns\InspectorCases;
use Tests\Tooling\Console\Inspectors\Contracts\ForInspector;
use Tooling\PHPStan\Console\Inspector as PHPStanInspector;

class InspectorTest extends TestCase implements ForInspector
{
    use InspectorCases;

    public string $class { get => PHPStanInspector::class; }

    public string $path { get => '/usr/local/bin/phpstan'; }

    public array $arguments {
        get => [
            ['name' => 'paths', 'isArray' => true, 'configValue' => ['/custom/path']],
        ];
    }

    public array $options {
        get => [
            ['name' => 'level', 'configValue' => '9'],
            ['name' => 'configuration'],
        ];
    }

    #[Test]
    public function it_includes_phpstan_specific_options(): void
    {
        $optionNames = $this->inspector->options->map(fn (InputOption $o) => $o->getName());

        $this->assertTrue(
            $optionNames->contains('configuration') || $optionNames->contains('level'),
            'Expected at least one PHPStan-specific option like "configuration" or "level".'
        );
    }
}
