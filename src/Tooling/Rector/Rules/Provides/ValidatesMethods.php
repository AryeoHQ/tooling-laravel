<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use ReflectionClass;

trait ValidatesMethods
{
    protected function hasMethod(ClassLike $node, string $expected): bool
    {
        return $this->hasMethodDirectly($node, $expected) || $this->hasMethodDeeply($node, $expected);
    }

    protected function doesNotHaveMethod(ClassLike $node, string $expected): bool
    {
        return ! $this->hasMethod($node, $expected);
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

    private function hasMethodDeeply(ClassLike $node, string $expected): bool
    {
        $className = $node->namespacedName !== null
            ? $node->namespacedName->toString()
            : ($node->name?->toString() ?? null);

        if ($className === null) {
            return false;
        }

        if (! $this->classExists($className)) {
            return false;
        }

        $reflection = new ReflectionClass($className);

        return $reflection->hasMethod($expected);
    }
}
