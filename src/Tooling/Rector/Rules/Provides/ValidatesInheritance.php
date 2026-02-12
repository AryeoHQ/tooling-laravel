<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use ReflectionClass;

trait ValidatesInheritance
{
    /**
     * @param  string|array<int, string>  $expected
     */
    protected function inherits(Class_|Enum_ $node, string|array $expected): bool
    {
        return $this->inheritsDirectly($node, $expected) || $this->inheritsDeeply($node, $expected);
    }

    /**
     * @param  string|array<int, string>  $expected
     */
    protected function doesNotInherit(Class_|Enum_ $node, string|array $expected): bool
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
            if ($reflection->getName() === $item || class_basename($reflection->getName()) === $item) {
                return true;
            }

            if ($this->classExists($item) && $reflection->isSubclassOf($item)) {
                return true;
            }

            foreach ($reflection->getInterfaceNames() as $interface) {
                if ($interface === $item || class_basename($interface) === $item) {
                    return true;
                }
            }

            foreach ($this->getAllTraits($reflection) as $trait) {
                if ($trait === $item || class_basename($trait) === $item) {
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
