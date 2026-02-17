<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;

trait EnsuresMethods
{
    /**
     * @param  string  $returnType  The return type of the method. (bool, int|bool, etc.)
     * @param  int  $visibility  (@see PhpParser\Modifiers)
     */
    public function ensureMethodIsDefined(Node\Stmt\ClassLike $node, string $method, string $returnType, int $visibility = Modifiers::PUBLIC): Node\Stmt\ClassLike
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

    public function ensureMethodIsNotDefined(ClassLike $node, string $method): ClassLike
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
