<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use Illuminate\Support\Str;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Throwable;

trait EnsuresTraits
{
    public function ensureTraitIsUsed(Class_ $node, string $trait): Class_
    {
        if ($this->inherits($node, $trait)) {
            return $node;
        }

        try {
            $this->useNodesToAddCollector->addUseImport(
                new FullyQualifiedObjectType($trait)
            );
        } catch (Throwable $e) {
            // continue without adding the use statement
        }

        $traitNode = new Name(Str::afterLast($trait, '\\'));
        $traitUseNode = new TraitUse([$traitNode]);

        if ($node->stmts === []) {
            $node->stmts = [$traitUseNode];
        } else {
            array_unshift($node->stmts, $traitUseNode);
        }

        return $node;
    }
}
