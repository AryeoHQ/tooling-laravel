<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node;
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

trait EnsuresAttributes
{
    public function ensureAttributeIsDefined(ArrowFunction|ClassConst|ClassLike|ClassMethod|Closure|Const_|EnumCase|Function_|Param|Property|PropertyHook $node, string $attribute): Node
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
}
