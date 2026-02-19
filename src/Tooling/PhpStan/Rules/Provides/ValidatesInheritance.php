<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Provides;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;

trait ValidatesInheritance
{
    /**
     * @param  string|array<int, string>  $expected
     */
    final protected function inherits(Class_|Enum_|Interface_|Trait_|ClassReflection $node, string|array $expected): bool
    {
        if ($node instanceof ClassReflection) {
            return $this->inheritsViaReflection($node, $expected);
        }

        return $this->inheritsDirectly($node, $expected) || $this->inheritsDeeply($node, $expected);
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    final protected function doesNotInherit(Class_|Enum_|Interface_|Trait_|ClassReflection $node, string|array $expected): bool
    {
        return ! $this->inherits($node, $expected);
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    private function inheritsDirectly(Class_|Enum_|Interface_|Trait_ $node, string|array $expected): bool
    {
        $items = is_array($expected) ? $expected : [$expected];

        foreach ($items as $item) {
            if ($node instanceof Class_ && $this->extendsClass($node, $item)) {
                return true;
            }

            if ($node instanceof Interface_ && $this->extendsInterface($node, $item)) {
                return true;
            }

            if ($node instanceof Class_ || $node instanceof Enum_) {
                if ($this->implementsInterface($node, $item)) {
                    return true;
                }
            }

            if (! $node instanceof Interface_ && $this->usesTrait($node, $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    private function inheritsDeeply(Class_|Enum_|Interface_|Trait_ $node, string|array $expected): bool
    {
        $className = $node->namespacedName?->toString();

        if ($className === null) {
            return false;
        }

        $classReflection = (new ObjectType($className))->getClassReflection();

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

        $parentName = ltrim($node->extends->toString(), '\\');

        return strcasecmp($parentName, ltrim($expected, '\\')) === 0;
    }

    private function extendsInterface(Interface_ $node, string $expected): bool
    {
        $normalized = ltrim($expected, '\\');

        foreach ($node->extends as $extend) {
            if (strcasecmp(ltrim($extend->toString(), '\\'), $normalized) === 0) {
                return true;
            }
        }

        return false;
    }

    private function implementsInterface(Class_|Enum_ $node, string $interface): bool
    {
        if ($node->implements === []) {
            return false;
        }

        foreach ($node->implements as $implementedInterface) {
            if (strcasecmp(ltrim($implementedInterface->toString(), '\\'), ltrim($interface, '\\')) === 0) {
                return true;
            }
        }

        return false;
    }

    private function usesTrait(Class_|Enum_|Trait_ $node, string $trait): bool
    {
        if ($node->stmts === []) {
            return false;
        }

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\TraitUse) {
                foreach ($stmt->traits as $implementedTrait) {
                    if (strcasecmp(ltrim($implementedTrait->toString(), '\\'), ltrim($trait, '\\')) === 0) {
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
