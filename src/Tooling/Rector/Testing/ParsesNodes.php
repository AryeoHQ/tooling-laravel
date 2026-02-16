<?php

declare(strict_types=1);

namespace Tooling\Rector\Testing;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

trait ParsesNodes
{
    protected function getClassNode(string $path): null|Class_
    {
        return $this->findNode($path, Class_::class);
    }

    protected function getEnumNode(string $path): null|Enum_
    {
        return $this->findNode($path, Enum_::class);
    }

    /**
     * @template T of Class_|Enum_
     *
     * @param  class-string<T>  $type
     * @return T|null
     */
    private function findNode(string $path, string $type): null|Class_|Enum_
    {
        $nodes = (new ParserFactory)->createForNewestSupportedVersion()->parse(file_get_contents($path));

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);
        $nodes = $traverser->traverse($nodes);

        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof $type) {
                        return $stmt;
                    }
                }
            }
        }

        return null;
    }
}
