<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;

trait ValidatesMethods
{
    final protected function hasMethod(Class_|Enum_|Interface_|Trait_ $node, string $expected): bool
    {
        return $this->hasMethodDirectly($node, $expected) || $this->hasMethodDeeply($node, $expected);
    }

    final protected function doesNotHaveMethod(Class_|Enum_|Interface_|Trait_ $node, string $expected): bool
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
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();

        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        if (! $classReflection->hasNativeMethod($expected)) {
            return false;
        }

        return ! $classReflection->getNativeMethod($expected)->isAbstract();
    }
}
