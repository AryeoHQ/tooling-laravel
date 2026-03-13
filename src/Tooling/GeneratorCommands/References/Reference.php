<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\References\Concerns\ManagesNamespace;
use Tooling\GeneratorCommands\References\Concerns\ResolvesPaths;
use Tooling\GeneratorCommands\References\Contracts\Reference as ReferenceContract;

abstract class Reference implements ReferenceContract
{
    use ManagesNamespace;
    use ResolvesPaths;

    final public Stringable $name;

    final public function __construct(Stringable|string $name, Stringable|string $baseNamespace)
    {
        $this->name = str($name);
        $this->baseNamespace = $baseNamespace;
    }

    abstract public null|Stringable $subNamespace { get; }

    public Stringable $namespace {
        get => $this->subNamespace !== null
            ? $this->baseNamespace->append('\\', $this->subNamespace->toString())
            : $this->baseNamespace;
    }

    public Stringable $fqcn {
        get => $this->namespace->append('\\', $this->name->toString());
    }
}
