<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;

trait EnsuresTraits
{
    public function ensureTraitIsUsed(Class_ $node, string $trait): Class_
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
}
