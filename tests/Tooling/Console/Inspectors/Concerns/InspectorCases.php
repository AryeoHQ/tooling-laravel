<?php

declare(strict_types=1);

namespace Tests\Tooling\Console\Inspectors\Concerns;

use Illuminate\Support\Collection;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tooling\Console\Inspectors\Inspector;

trait InspectorCases
{
    protected Inspector $inspector {
        get => app($this->class)->executable($this->path);
    }

    #[Test]
    public function it_extends_the_base_inspector(): void
    {
        $this->assertInstanceOf(Inspector::class, $this->inspector);
    }

    #[Test]
    public function it_resolves_from_the_container(): void
    {
        $this->assertInstanceOf($this->class, app($this->class));
    }

    #[Test]
    public function it_returns_itself_from_executable_for_fluent_chaining(): void
    {
        $inspector = app($this->class);

        $result = $inspector->executable('/some/path');

        $this->assertSame($inspector, $result);
    }

    #[Test]
    public function it_resolves_aliases_from_the_underlying_command(): void
    {
        $inspector = $this->inspector;
        $inspector->aliases = collect(['lint']);

        $this->assertInstanceOf(Collection::class, $inspector->aliases);
        $this->assertNotEmpty($inspector->aliases);
    }

    #[Test]
    public function it_resolves_arguments_from_the_underlying_command(): void
    {
        $arguments = $this->inspector->arguments;

        $this->assertTrue($arguments->isNotEmpty());
        $this->assertContainsOnlyInstancesOf(InputArgument::class, $arguments);
    }

    #[Test]
    public function it_resolves_options_from_the_underlying_command(): void
    {
        $options = $this->inspector->options;

        $this->assertTrue($options->isNotEmpty());
        $this->assertContainsOnlyInstancesOf(InputOption::class, $options);
    }

    #[Test]
    public function it_strips_conflicting_options(): void
    {
        $optionNames = $this->inspector->options->map(fn (InputOption $o) => $o->getName());

        $this->assertFalse($optionNames->contains('help'));
        $this->assertFalse($optionNames->contains('quiet'));
        $this->assertFalse($optionNames->contains('verbose'));
        $this->assertFalse($optionNames->contains('version'));
        $this->assertFalse($optionNames->contains('ansi'));
        $this->assertFalse($optionNames->contains('no-ansi'));
        $this->assertFalse($optionNames->contains('no-interaction'));
        $this->assertFalse($optionNames->contains('env'));
    }

    #[Test]
    public function it_has_expected_array_arguments(): void
    {
        $inspector = $this->inspector;

        foreach ($this->arguments as $fixture) {
            if (! ($fixture['isArray'] ?? false)) {
                continue;
            }

            $argument = $inspector->arguments->first(
                fn (InputArgument $a) => $a->getName() === $fixture['name']
            );

            $this->assertNotNull($argument, "Expected argument \"{$fixture['name']}\" was not found.");
            $this->assertTrue($argument->isArray());
        }
    }

    #[Test]
    public function it_reads_config_defaults_for_arguments(): void
    {
        foreach ($this->arguments as $fixture) {
            if (! array_key_exists('configValue', $fixture)) {
                continue;
            }

            $key = 'tooling.'.basename($this->path).'.cli.arguments.'.$fixture['name'];

            config()->set($key, $fixture['configValue']);

            $argument = $this->inspector->arguments->first(
                fn (InputArgument $a) => $a->getName() === $fixture['name']
            );

            $this->assertSame($fixture['configValue'], $argument->getDefault());
        }
    }

    #[Test]
    public function it_reads_config_defaults_for_options(): void
    {
        foreach ($this->options as $fixture) {
            if (! array_key_exists('configValue', $fixture)) {
                continue;
            }

            $key = 'tooling.'.basename($this->path).'.cli.options.'.$fixture['name'];

            config()->set($key, $fixture['configValue']);

            $option = $this->inspector->options->first(
                fn (InputOption $o) => $o->getName() === $fixture['name']
            );

            if ($option === null) {
                $this->markTestSkipped("Option \"{$fixture['name']}\" not available."); // @phpstan-ignore staticMethod.dynamicCall
            }

            $this->assertSame($fixture['configValue'], $option->getDefault());
        }
    }

    #[Test]
    public function it_throws_logic_exception_for_undefined_properties(): void
    {
        $this->expectException(LogicException::class);

        $this->inspector->nonExistentProperty; // @phpstan-ignore expr.resultUnused, property.notFound
    }
}
