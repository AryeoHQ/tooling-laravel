<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Provides;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

trait ParsesNodes
{
    protected function getClassNode(string $path): null|Class_
    {
        $classContent = file_get_contents($path);
        $nodes = $this->parse($classContent);

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);
        $nodes = $traverser->traverse($nodes);

        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof Class_) {
                        return $stmt;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return \PhpParser\Node\Stmt[]|null
     */
    private function parse(string $code): null|array
    {
        $parser = (new ParserFactory)->createForNewestSupportedVersion();

        return $parser->parse($code);
    }
}
