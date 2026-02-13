<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;

trait ValidatesAttributes
{
    /** @param class-string $attribute */
    public function hasAttribute(ClassLike|ClassMethod|Function_|Property $node, string $attribute): bool
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
