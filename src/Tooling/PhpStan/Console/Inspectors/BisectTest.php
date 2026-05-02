<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Inspectors;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Input\InputOption;
use Tests\TestCase;
use Tooling\Console\Testing\Attributes\ExpectsArguments;
use Tooling\Console\Testing\Concerns\InspectorTestCases;
use Tooling\Console\Testing\Contracts\ForInspector;

#[ExpectsArguments]
class BisectTest extends TestCase implements ForInspector
{
    use InspectorTestCases;

    public string $class = Bisect::class;

    public string $path = '/usr/local/bin/phpstan';

    public array $arguments {
        get => [
            ['name' => 'paths', 'isArray' => true, 'configValue' => ['/custom/path']],
        ];
    }

    public array $options {
        get => [
            ['name' => 'configuration'],
            ['name' => 'level', 'configValue' => '9'],
            ['name' => 'good'],
            ['name' => 'bad'],
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
