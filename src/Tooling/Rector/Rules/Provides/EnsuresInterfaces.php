<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use Illuminate\Support\Str;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Throwable;

trait EnsuresInterfaces
{
    public function ensureInterfaceIsImplemented(Class_ $node, string $interface): Class_
    {
        if ($this->inherits($node, $interface)) {
            return $node;
        }

        try {
            $this->useNodesToAddCollector->addUseImport(
                new FullyQualifiedObjectType($interface)
            );
        } catch (Throwable $e) {
            // continue without adding the use statement
        }

        $interfaceNode = new Name(Str::afterLast($interface, '\\'));

        if ($node->implements === []) {
            $node->implements = [$interfaceNode];
        } else {
            $node->implements[] = $interfaceNode;
        }

        return $node;
    }
}
