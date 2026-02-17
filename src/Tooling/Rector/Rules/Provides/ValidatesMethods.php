<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;

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
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();

        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        return $classReflection->hasMethod($expected);
    }
}
