<?php

declare(strict_types=1);

namespace Tooling\Console\Inspectors;

use AllowDynamicProperties;
use Illuminate\Support\Collection;
use LogicException;
use ReflectionAttribute;
use ReflectionObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tooling\Console\Inspectors\Attributes\Conflicts;

/**
 * @property Command $command
 */
#[AllowDynamicProperties]
abstract class Inspector
{
    protected readonly string $executable;

    protected ReflectionObject $reflection { get => $this->reflection ??= new ReflectionObject($this); }

    /** @var Collection<array-key, string> */
    public Collection $aliases { get => $this->aliases ??= collect($this->command->getAliases()); }

    /** @var Collection<array-key, InputArgument> */
    public Collection $arguments {
        get => $this->arguments ??= collect((array) $this->command->getDefinition()->getArguments())->map(
            fn ($argument) => $this->makeArgument($argument)
        );
    }

    /** @var Collection<array-key, InputOption> */
    public Collection $options {
        get => $this->options ??= collect((array) $this->command->getDefinition()->getOptions())->reject(
            fn ($option): bool => $this->optionConflicts->list->contains($option->getName())
        )->map(
            fn ($option): InputOption => $this->makeOption($option)
        );
    }

    protected Conflicts\Options $optionConflicts {
        get => with(
            data_get($this->reflection->getAttributes(Conflicts\Options::class), 0),
            fn (null|ReflectionAttribute $attribute) => $attribute?->newInstance() ?? new Conflicts\Options
        );
    }

    protected Conflicts\Shortcuts $shortcutConflicts {
        get => with(
            data_get($this->reflection->getAttributes(Conflicts\Shortcuts::class), 0),
            fn (null|ReflectionAttribute $attribute) => $attribute?->newInstance() ?? new Conflicts\Shortcuts
        );
    }

    public function executable(string $path): static
    {
        $this->executable = $path;

        return $this;
    }

    protected function config(null|string $key = null, mixed $default = null): mixed
    {
        $key = collect([
            'tooling', str($this->executable)->afterLast('/'), 'cli', $key,
        ])->filter()->implode('.');

        return config($key, $default);
    }

    protected function makeArgument(mixed $argument): InputArgument
    {
        return new InputArgument(
            $argument->getName(),
            $this->determineArgumentMode($argument),
            $argument->getDescription(),
            $this->config('arguments.'.$argument->getName(), $argument->getDefault())
        );
    }

    /**
     * @param  InputArgument  $argument
     * @return int<0, 7>
     */
    protected function determineArgumentMode(object $argument): int
    {
        $mode = $argument->isRequired() ? InputArgument::REQUIRED : InputArgument::OPTIONAL;

        if ($argument->isArray()) {
            $mode |= InputArgument::IS_ARRAY;
        }

        return $mode;
    }

    /**
     * @param  InputOption  $option
     */
    protected function makeOption($option): InputOption
    {
        $mode = $this->determineOptionMode($option);

        return new InputOption(
            $option->getName(),
            when($this->shortcutConflicts->list->contains($option->getShortcut()), null, $option->getShortcut()),
            $mode,
            $option->getDescription(),
            $mode === InputOption::VALUE_NONE
                ? null
                : $this->config('options.'.$option->getName(), $option->getDefault())
        );
    }

    /**
     * @param  InputOption  $option
     * @return int<0, 31>
     */
    protected function determineOptionMode(object $option): int
    {
        return $option->isValueRequired() ? InputOption::VALUE_REQUIRED :
            ($option->isValueOptional() ? InputOption::VALUE_OPTIONAL : InputOption::VALUE_NONE);
    }

    public function __get(string $name)
    {
        throw new LogicException('Property not found.');
    }
}
