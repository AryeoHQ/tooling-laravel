<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Provides;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;

trait ValidatesAttributes
{
    public function classHasAttribute(Class_ $node, string $attribute): bool
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

    public function methodHasAttribute(string $attribute, null|Node\Stmt\ClassMethod $method = null): bool
    {
        if ($method === null) {
            return false;
        }

        if ($method->attrGroups === []) {
            return false;
        }

        foreach ($method->attrGroups as $attrGroup) {
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
