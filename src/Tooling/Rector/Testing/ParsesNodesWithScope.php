<?php

declare(strict_types=1);

namespace Tooling\Rector\Testing;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;

trait ParsesNodesWithScope
{
    use ResolvesRectorRules;

    protected function getClassNodeWithScope(string $path): null|Class_
    {
        return $this->findNodeWithScope($path, Class_::class);
    }

    protected function getEnumNodeWithScope(string $path): null|Enum_
    {
        return $this->findNodeWithScope($path, Enum_::class);
    }

    /**
     * @template T of Class_|Enum_
     *
     * @param  class-string<T>  $type
     * @return T|null
     */
    private function findNodeWithScope(string $path, string $type): null|Class_|Enum_
    {
        $stmts = (new ParserFactory)->createForNewestSupportedVersion()->parse(file_get_contents($path));

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);
        $stmts = $traverser->traverse($stmts);

        /** @var array<Stmt> $stmts */
        $stmts = array_filter($stmts, fn ($node) => $node instanceof Stmt);

        if ($this->rectorConfig === null) {
            $this->setUpResolvesRectorRules();
        }

        $scopeResolver = $this->rectorConfig->make(PHPStanNodeScopeResolver::class);
        $stmts = $scopeResolver->processNodes($stmts, $path);

        foreach ($stmts as $node) {
            if ($node instanceof Namespace_) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof $type) {
                        return $stmt;
                    }
                }
            }

            if ($node instanceof $type) {
                return $node;
            }
        }

        return null;
    }
}
