<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

use Illuminate\Support\Stringable;

class GenericClass extends Reference
{
    public null|Stringable $subNamespace {
        get => null;
    }

    public TestClass $test {
        get => new TestClass(
            name: $this->name->append('Test'),
            baseNamespace: $this->namespace,
        );
    }

    public static function fromFqcn(Stringable|string $fqcn): static
    {
        $fqcn = str($fqcn)->start('\\');
        $name = str(class_basename($fqcn->toString()));
        $namespace = $fqcn->beforeLast('\\');

        $instance = new static(name: $name, baseNamespace: $namespace); // @phpstan-ignore new.static

        if ($instance->subNamespace !== null) {
            $suffix = '\\'.$instance->subNamespace->toString();

            $instance->baseNamespace = match ($namespace->endsWith($suffix)) {
                true => $namespace->beforeLast($suffix),
                false => throw new \InvalidArgumentException(
                    "[{$fqcn}] does not end with the expected sub-namespace [{$instance->subNamespace}].",
                ),
            };
        }

        return $instance;
    }
}
