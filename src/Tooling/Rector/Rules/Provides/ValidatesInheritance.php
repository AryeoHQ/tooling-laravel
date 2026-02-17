<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;

trait ValidatesInheritance
{
    /**
     * @param  string|array<int, string>  $expected
     */
    final protected function inherits(Class_|Enum_ $node, string|array $expected): bool
    {
        return $this->inheritsDirectly($node, $expected) || $this->inheritsDeeply($node, $expected);
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    final protected function doesNotInherit(Class_|Enum_ $node, string|array $expected): bool
    {
        return ! $this->inherits($node, $expected);
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    private function inheritsDirectly(Class_|Enum_ $node, string|array $expected): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        $items = is_array($expected) ? $expected : [$expected];

        foreach ($items as $item) {
            if ($this->extendsClass($node, $item)) {
                return true;
            }

            if ($this->implementsInterface($node, $item)) {
                return true;
            }

            if ($this->usesTrait($node, $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    private function inheritsDeeply(Class_|Enum_ $node, string|array $expected): bool
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();

        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        return $this->inheritsViaReflection($classReflection, $expected);
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    private function inheritsViaReflection(ClassReflection $reflection, string|array $expected): bool
    {
        $items = is_array($expected) ? $expected : [$expected];

        foreach ($items as $item) {
            $normalizedItem = ltrim($item, '\\');

            if (ltrim($reflection->getName(), '\\') === $normalizedItem) {
                return true;
            }

            foreach ($reflection->getParents() as $parent) {
                if (ltrim($parent->getName(), '\\') === $normalizedItem) {
                    return true;
                }
            }

            foreach ($reflection->getInterfaces() as $interface) {
                if (ltrim($interface->getName(), '\\') === $normalizedItem) {
                    return true;
                }
            }

            foreach ($this->getAllTraits($reflection) as $trait) {
                if (ltrim($trait->getName(), '\\') === $normalizedItem) {
                    return true;
                }
            }
        }

        return false;
    }

    private function extendsClass(Class_ $node, string $expected): bool
    {
        if ($node->extends === null) {
            return false;
        }

        return strcasecmp($node->extends->toString(), ltrim($expected, '\\')) === 0;
    }

    private function implementsInterface(Class_ $node, string $interface): bool
    {
        if ($node->implements === []) {
            return false;
        }

        $expected = ltrim($interface, '\\');

        foreach ($node->implements as $implementedInterface) {
            if ($implementedInterface->toString() === $expected) {
                return true;
            }
        }

        return false;
    }

    private function usesTrait(Class_ $node, string $trait): bool
    {
        if ($node->stmts === []) {
            return false;
        }

        $expected = ltrim($trait, '\\');

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\TraitUse) {
                foreach ($stmt->traits as $implementedTrait) {
                    if ($implementedTrait->toString() === $expected) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return array<string, ClassReflection>
     */
    private function getAllTraits(ClassReflection $reflection): array
    {
        $traits = $reflection->getTraits();

        foreach ($reflection->getParents() as $parent) {
            $traits = array_merge($traits, $parent->getTraits());
        }

        foreach ($traits as $trait) {
            $traits = array_merge($traits, $this->getAllTraits($trait));
        }

        return $traits;
    }
}
