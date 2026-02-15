<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;

trait EnsuresInterfaces
{
    public function ensureInterfaceIsImplemented(Class_ $node, string $interface): Class_
    {
        if ($this->inherits($node, $interface)) {
            return $node;
        }

        $interfaceNode = new FullyQualified(ltrim($interface, '\\'));

        if ($node->implements === []) {
            $node->implements = [$interfaceNode];
        } else {
            $node->implements[] = $interfaceNode;
        }

        return $node;
    }
}
