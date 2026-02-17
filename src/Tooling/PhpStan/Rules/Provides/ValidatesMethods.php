<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Provides;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;

trait ValidatesMethods
{
    protected function hasMethod(ClassLike|ClassReflection $node, string $expected): bool
    {
        if ($node instanceof ClassReflection) {
            return $this->hasMethodViaReflection($node, $expected);
        }

        return $this->hasMethodDirectly($node, $expected) || $this->hasMethodDeeply($node, $expected);
    }

    protected function doesNotHaveMethod(ClassLike|ClassReflection $node, string $expected): bool
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
        $className = $node->namespacedName?->toString();

        if ($className === null) {
            return false;
        }

        $classReflection = (new ObjectType($className))->getClassReflection();

        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        return $this->hasMethodViaReflection($classReflection, $expected);
    }

    private function hasMethodViaReflection(ClassReflection $reflection, string $expected): bool
    {
        return $reflection->hasMethod($expected);
    }
}
