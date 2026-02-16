<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;

trait ValidatesMethods
{
    protected function hasMethod(ClassLike $node, string $expected): bool
    {
        return $this->hasMethodDirectly($node, $expected);
    }

    private function hasMethodDirectly(ClassLike $node, string $expected): bool
    {
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof ClassMethod && $stmt->name->toString() === $expected) {
                return true;
            }
        }

        return false;
    }
}
