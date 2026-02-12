<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Provides;

use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

trait ValidatesInheritance
{
    /**
     * @param  string|array<int, string>  $expected
     */
    protected function inherits(Class_|Enum_|ClassReflection $node, string|array $expected, ReflectionProvider $reflectionProvider): bool
    {
        if ($node instanceof ClassReflection) {
            return $this->inheritsViaReflection($node, $expected);
        }

        return $this->inheritsDirectly($node, $expected) || $this->inheritsDeeply($node, $expected, $reflectionProvider);
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    protected function doesNotInherit(Class_|Enum_|ClassReflection $node, string|array $expected, ReflectionProvider $reflectionProvider): bool
    {
        return ! $this->inherits($node, $expected, $reflectionProvider);
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    protected function inheritsDirectly(Class_|Enum_ $node, string|array $expected): bool
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

    private function extendsClass(Class_ $node, string $expected): bool
    {
        if ($node->extends === null) {
            return false;
        }

        $parentName = $node->extends->toString();

        return strcasecmp($parentName, $expected) === 0
            || strcasecmp(class_basename($parentName), $expected) === 0
            || strcasecmp(class_basename($parentName), class_basename($expected)) === 0;
    }

    private function implementsInterface(Class_ $node, string $interface): bool
    {
        if ($node->implements === []) {
            return false;
        }

        foreach ($node->implements as $implementedInterface) {
            $interfaceName = $implementedInterface->toString();

            if ($interfaceName === $interface) {
                return true;
            }

            if (Str::afterLast($interfaceName, '\\') === $interface) {
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

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\TraitUse) {
                foreach ($stmt->traits as $implementedTrait) {
                    $traitName = $implementedTrait->toString();

                    if ($traitName === $trait) {
                        return true;
                    }

                    if (Str::afterLast($traitName, '\\') === $trait) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    private function inheritsDeeply(Class_|Enum_ $node, string|array $expected, ReflectionProvider $reflectionProvider): bool
    {
        $className = $node->namespacedName !== null
            ? $node->namespacedName->toString()
            : ($node->name?->toString() ?? null);

        if ($className === null) {
            throw new \RuntimeException('Could not determine class name from node');
        }

        if (! $reflectionProvider->hasClass($className)) {
            return false;
        }

        return $this->inheritsViaReflection($reflectionProvider->getClass($className), $expected);
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    private function inheritsViaReflection(ClassReflection $reflection, string|array $expected): bool
    {
        $items = is_array($expected) ? $expected : [$expected];

        foreach ($items as $item) {
            if ($reflection->getName() === $item || class_basename($reflection->getName()) === $item) {
                return true;
            }

            foreach ($reflection->getParents() as $parent) {
                if ($parent->getName() === $item || class_basename($parent->getName()) === $item) {
                    return true;
                }
            }

            foreach ($reflection->getInterfaces() as $interface) {
                if ($interface->getName() === $item || class_basename($interface->getName()) === $item) {
                    return true;
                }
            }

            foreach ($this->getAllTraits($reflection) as $trait) {
                if ($trait->getName() === $item || class_basename($trait->getName()) === $item) {
                    return true;
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

        return $traits;
    }
}
