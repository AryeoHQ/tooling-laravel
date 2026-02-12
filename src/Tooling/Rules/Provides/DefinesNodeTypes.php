<?php

declare(strict_types=1);

namespace Tooling\Rules\Provides;

use Illuminate\Support\Collection;
use ReflectionClass;
use Tooling\Rules\Attributes\NodeType;

trait DefinesNodeTypes
{
    /** @var Collection<int, NodeType> */
    protected Collection $nodeTypes {
        get => $this->nodeTypes ??= collect(
            new ReflectionClass($this)->getAttributes(NodeType::class)
        )->map->newInstance();
    }
}
