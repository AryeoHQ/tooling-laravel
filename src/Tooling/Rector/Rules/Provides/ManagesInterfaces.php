<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;

trait ManagesInterfaces
{
    final protected function addInterface(Class_|Enum_|Interface_ $node, string $interface): Class_|Enum_|Interface_
    {
        if ($this->inherits($node, $interface)) {
            return $node;
        }

        $interfaceNode = new FullyQualified(ltrim($interface, '\\'));

        if ($node instanceof Interface_) {
            $node->extends[] = $interfaceNode;

            return $node;
        }

        $node->implements[] = $interfaceNode;

        return $node;
    }

    final protected function removeInterface(Class_|Enum_|Interface_ $node, string $interface): Class_|Enum_|Interface_
    {
        if ($this->doesNotInherit($node, $interface)) {
            return $node;
        }

        $expected = ltrim($interface, '\\');

        if ($node instanceof Interface_) {
            $node->extends = array_values(
                array_filter(
                    $node->extends,
                    fn (Name $name) => strcasecmp(ltrim($name->toString(), '\\'), $expected) !== 0
                )
            );

            return $node;
        }

        $node->implements = array_values(
            array_filter(
                $node->implements,
                fn (Name $name) => strcasecmp(ltrim($name->toString(), '\\'), $expected) !== 0
            )
        );

        return $node;
    }
}
