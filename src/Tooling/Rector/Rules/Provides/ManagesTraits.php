<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;

trait ManagesTraits
{
    final protected function addTrait(Class_|Enum_|Trait_ $node, string $trait): Class_|Enum_|Trait_
    {
        if ($this->inherits($node, $trait)) {
            return $node;
        }

        $traitNode = new FullyQualified(ltrim($trait, '\\'));
        $traitUseNode = new TraitUse([$traitNode]);

        if ($node->stmts === []) {
            $node->stmts = [$traitUseNode];
        } else {
            array_unshift($node->stmts, $traitUseNode);
        }

        return $node;
    }

    final protected function removeTrait(Class_|Enum_|Trait_ $node, string $trait): Class_|Enum_|Trait_
    {
        if ($this->doesNotInherit($node, $trait)) {
            return $node;
        }

        $expected = ltrim($trait, '\\');

        $node->stmts = collect($node->stmts)
            ->map(function (Node\Stmt $stmt) use ($expected): ?Node\Stmt {
                if (! $stmt instanceof TraitUse) {
                    return $stmt;
                }

                $stmt->traits = array_values(
                    array_filter(
                        $stmt->traits,
                        fn (Node\Name $name) => strcasecmp(ltrim($name->toString(), '\\'), $expected) !== 0
                    )
                );

                return $stmt->traits !== [] ? $stmt : null;
            })
            ->filter()
            ->toArray();

        return $node;
    }
}
