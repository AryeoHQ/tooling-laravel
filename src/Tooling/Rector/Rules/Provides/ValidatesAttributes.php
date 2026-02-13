<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyHook;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;

trait ValidatesAttributes
{
    /** @param class-string $attribute */
    public function hasAttribute(ArrowFunction|ClassConst|ClassLike|ClassMethod|Closure|Const_|EnumCase|Function_|Param|Property|PropertyHook $node, string $attribute): bool
    {
        if ($node->attrGroups === []) {
            return false;
        }

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name instanceof FullyQualified) {
                    if ($attr->name->toString() === $attribute) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
