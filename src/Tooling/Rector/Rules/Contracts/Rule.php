<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Contracts;

use PhpParser\Node;

/**
 * @template TNodeType of \PhpParser\Node
 */
interface Rule
{
    /**
     * @param  TNodeType  $node
     * @return TNodeType|null
     */
    public function handle(Node $node): null|Node;

    /**
     * @param  TNodeType  $node
     */
    public function shouldHandle(Node $node): bool;
}
