<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use ReflectionClass;

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
        $className = $node->namespacedName !== null
            ? $node->namespacedName->toString()
            : ($node->name?->toString() ?? null);

        if ($className === null) {
            throw new \RuntimeException('Could not determine class name from node');
        }

        if (! $this->classExists($className)) {
            throw new \RuntimeException("Class '$className' does not exist or could not be autoloaded");
        }

        $reflection = new ReflectionClass($className);
        $items = is_array($expected) ? $expected : [$expected];

        foreach ($items as $item) {
            $normalizedItem = ltrim($item, '\\');

            if ($reflection->getName() === $normalizedItem) {
                return true;
            }

            if ($this->classExists($normalizedItem) && $reflection->isSubclassOf($normalizedItem)) {
                return true;
            }

            foreach ($reflection->getInterfaceNames() as $interface) {
                if ($interface === $normalizedItem) {
                    return true;
                }
            }

            foreach ($this->getAllTraits($reflection) as $trait) {
                if ($trait === $normalizedItem) {
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

    private function classExists(string $className): bool
    {
        return class_exists($className, true)
            || interface_exists($className, true)
            || trait_exists($className, true)
            || enum_exists($className, true);
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     * @return array<string>
     */
    private function getAllTraits(ReflectionClass $reflection): array
    {
        $traits = [];

        do {
            $traits = array_merge($traits, $reflection->getTraitNames());
        } while ($reflection = $reflection->getParentClass());

        return array_unique($traits);
    }
}
