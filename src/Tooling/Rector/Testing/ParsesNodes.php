<?php

declare(strict_types=1);

namespace Tooling\Rector\Testing;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
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

    protected function getInterfaceNode(string $path): null|Interface_
    {
        return $this->findNode($path, Interface_::class);
    }

    protected function getTraitNode(string $path): null|Trait_
    {
        return $this->findNode($path, Trait_::class);
    }

    /**
     * @template T of Class_|Enum_|Interface_|Trait_
     *
     * @param  class-string<T>  $type
     * @return T|null
     */
    private function findNode(string $path, string $type): null|Class_|Enum_|Interface_|Trait_
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
