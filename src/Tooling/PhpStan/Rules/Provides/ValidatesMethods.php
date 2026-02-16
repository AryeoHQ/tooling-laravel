<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Provides;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

trait ValidatesMethods
{
    protected function hasMethod(ClassLike|ClassReflection $node, string $expected, ReflectionProvider $reflectionProvider): bool
    {
        if ($node instanceof ClassReflection) {
            return $this->hasMethodViaReflection($node, $expected);
        }

        return $this->hasMethodDirectly($node, $expected) || $this->hasMethodDeeply($node, $expected, $reflectionProvider);
    }

    protected function doesNotHaveMethod(ClassLike|ClassReflection $node, string $expected, ReflectionProvider $reflectionProvider): bool
    {
        return ! $this->hasMethod($node, $expected, $reflectionProvider);
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

    private function hasMethodDeeply(ClassLike $node, string $expected, ReflectionProvider $reflectionProvider): bool
    {
        $className = $node->namespacedName !== null
            ? $node->namespacedName->toString()
            : ($node->name?->toString() ?? null);

        if ($className === null) {
            return false;
        }

        if (! $reflectionProvider->hasClass($className)) {
            return false;
        }

        return $this->hasMethodViaReflection($reflectionProvider->getClass($className), $expected);
    }

    private function hasMethodViaReflection(ClassReflection $reflection, string $expected): bool
    {
        return $reflection->hasMethod($expected);
    }
}
