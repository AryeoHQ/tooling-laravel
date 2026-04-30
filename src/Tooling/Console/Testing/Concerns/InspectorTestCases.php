<?php

declare(strict_types=1);

namespace Tooling\Console\Testing\Concerns;

use Illuminate\Support\Collection;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tooling\Console\Inspectors\Inspector;
use Tooling\Console\Testing\Attributes\ConfirmsArguments;
use Tooling\Console\Testing\Attributes\DoesntExpectArguments;
use Tooling\Console\Testing\Attributes\ExpectsArguments;

/**
 * @mixin \Tests\TestCase
 */
trait InspectorTestCases
{
    protected Inspector $inspector {
        get => app($this->class)->executable($this->path);
    }

    protected ConfirmsArguments $confirmsArguments {
        get => $this->confirmsArguments ??= collect(new ReflectionClass($this)->getAttributes())->filter(
            fn ($attribute) => is_a($attribute->getName(), ConfirmsArguments::class, true)
        )->when(
            fn (Collection $attributes) => $attributes->isEmpty(),
            fn () => $this->fail(
                class_basename(static::class).' must use a #[' . class_basename(ExpectsArguments::class) . '] or #[' . class_basename(DoesntExpectArguments::class) . '] attribute.'
            )
        )->first()->newInstance();
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
        $attribute = $this->confirmsArguments;
        $arguments = $this->inspector->arguments;

        if ($attribute instanceof DoesntExpectArguments) {
            $this->assertTrue($arguments->isEmpty());

            return;
        }

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
        if ($this->confirmsArguments instanceof DoesntExpectArguments) {
            $this->assertTrue($this->inspector->arguments->isEmpty());

            return;
        }

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
        if ($this->confirmsArguments instanceof DoesntExpectArguments) {
            $this->assertTrue($this->inspector->arguments->isEmpty());

            return;
        }

        foreach ($this->arguments as $fixture) {
            if (! array_key_exists('configValue', $fixture)) {
                continue;
            }

            $key = 'tooling.'.basename($this->path).'.cli.'.$this->class.'.arguments.'.$fixture['name'];

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

            $key = 'tooling.'.basename($this->path).'.cli.'.$this->class.'.options.'.$fixture['name'];

            config()->set($key, $fixture['configValue']);

            $option = $this->inspector->options->first(
                fn (InputOption $o) => $o->getName() === $fixture['name']
            );

            $this->assertNotNull($option, "Option \"{$fixture['name']}\" not found on the command definition.");
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
