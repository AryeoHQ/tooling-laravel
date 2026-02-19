<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Provides;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;

trait ValidatesMethods
{
    final protected function hasMethod(Class_|Enum_|Interface_|Trait_|ClassReflection $node, string $expected): bool
    {
        if ($node instanceof ClassReflection) {
            return $this->hasMethodViaReflection($node, $expected);
        }

        return $this->hasMethodDirectly($node, $expected) || $this->hasMethodDeeply($node, $expected);
    }

    final protected function doesNotHaveMethod(Class_|Enum_|Interface_|Trait_|ClassReflection $node, string $expected): bool
    {
        return ! $this->hasMethod($node, $expected);
    }

    private function hasMethodDirectly(Class_|Enum_|Interface_|Trait_ $node, string $expected): bool
    {
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof ClassMethod
                && $stmt->name->toString() === $expected
                && $stmt->stmts !== null) {
                return true;
            }
        }

        return false;
    }

    private function hasMethodDeeply(Class_|Enum_|Interface_|Trait_ $node, string $expected): bool
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
        if (! $reflection->hasNativeMethod($expected)) {
            return false;
        }

        return ! $reflection->getNativeMethod($expected)->isAbstract();
    }
}
