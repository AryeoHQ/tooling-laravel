<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
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

trait ManagesAttributes
{
    final protected function addAttribute(ArrowFunction|ClassConst|ClassLike|ClassMethod|Closure|Const_|EnumCase|Function_|Param|Property|PropertyHook $node, string $attribute): ArrowFunction|ClassConst|ClassLike|ClassMethod|Closure|Const_|EnumCase|Function_|Param|Property|PropertyHook
    {
        if ($this->hasAttribute($node, $attribute)) {
            return $node;
        }

        $attributeName = new FullyQualified(ltrim($attribute, '\\'));
        $attr = new Attribute($attributeName, []);
        $attrGroup = new AttributeGroup([$attr]);

        if ($node->attrGroups === []) {
            $node->attrGroups = [$attrGroup];
        } else {
            $node->attrGroups[] = $attrGroup;
        }

        return $node;
    }

    final protected function removeAttribute(ArrowFunction|ClassConst|ClassLike|ClassMethod|Closure|Const_|EnumCase|Function_|Param|Property|PropertyHook $node, string $attribute): ArrowFunction|ClassConst|ClassLike|ClassMethod|Closure|Const_|EnumCase|Function_|Param|Property|PropertyHook
    {
        if ($node->attrGroups === []) {
            return $node;
        }

        $expected = ltrim($attribute, '\\');

        foreach ($node->attrGroups as $key => $attrGroup) {
            foreach ($attrGroup->attrs as $attrKey => $attr) {
                if ($attr->name instanceof FullyQualified && $attr->name->toString() === $expected) {
                    unset($attrGroup->attrs[$attrKey]);
                }
            }

            if ($attrGroup->attrs === []) {
                unset($node->attrGroups[$key]);
            }
        }

        $node->attrGroups = array_values($node->attrGroups);

        return $node;
    }
}
