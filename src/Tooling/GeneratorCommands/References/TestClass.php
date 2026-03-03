<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

use Illuminate\Support\Stringable;
use Tooling\GeneratorCommands\References\Contracts\Reference;

final class TestClass
{
    private readonly Reference $parent;

    public function __construct(Reference $reference)
    {
        $this->parent = $reference;
    }

    public Stringable $name {
        get => $this->parent->name->append('Test');
    }

    public Stringable $namespace {
        get => $this->parent->namespace;
    }

    public Stringable $fqcn {
        get => $this->namespace->append('\\', $this->name->toString());
    }

    public Stringable $directoryPath {
        get => $this->parent->directoryPath;
    }

    public Stringable $filePath {
        get => $this->directoryPath->append('/', $this->name->toString(), '.php');
    }
}
