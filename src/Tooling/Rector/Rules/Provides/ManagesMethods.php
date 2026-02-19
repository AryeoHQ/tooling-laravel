<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

trait ManagesMethods
{
    /**
     * @param  string  $returnType  The return type of the method. (bool, int|bool, etc.)
     * @param  int  $visibility  (@see PhpParser\Modifiers)
     */
    final protected function addMethod(Class_|Enum_|Interface_|Trait_ $node, string $method, string $returnType, int $visibility = Modifiers::PUBLIC): Class_|Enum_|Interface_|Trait_
    {
        if ($this->hasMethod($node, $method)) {
            return $node;
        }

        $node->stmts[] = new ClassMethod($method, [
            'flags' => $visibility,
            'returnType' => $this->buildReturnType($returnType),
            'stmts' => [],
        ]);

        return $node;
    }

    final protected function removeMethod(Class_|Enum_|Interface_|Trait_ $node, string $method): Class_|Enum_|Interface_|Trait_
    {
        if (! $this->hasMethod($node, $method)) {
            return $node;
        }

        $node->stmts = collect($node->stmts)
            ->filter(function (Node\Stmt $stmt) use ($method): bool {
                if ($stmt instanceof ClassMethod && $stmt->name->toString() === $method) {
                    return false;
                }

                return true;
            })
            ->toArray();

        return $node;
    }

    private function buildReturnType(string $returnType): Node\Identifier|Node\UnionType
    {
        if (! str_contains($returnType, '|')) {
            return new Node\Identifier($returnType);
        }

        return new Node\UnionType(array_map(fn (string $type) => new Node\Identifier($type), explode('|', $returnType)));
    }
}
