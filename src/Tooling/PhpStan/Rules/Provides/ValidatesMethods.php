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
            return $this->inheritsViaReflection($node, $expected);
        }

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

    private function hasMethodViaReflection(ClassReflection $reflection, string $expected): bool
    {
        return $reflection->hasMethod($expected);
    }
}
