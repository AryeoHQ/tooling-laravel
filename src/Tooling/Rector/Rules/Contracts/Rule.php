<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Contracts;

use PhpParser\Node;

// TODO: We should use the `handle()` / `shouldHandle()` for both PHPStan and Rector rules.

/**
 * @template TNodeType of \PhpParser\Node
 */
interface Rule
{
    /**
     * @param  TNodeType  $node
     */
    public function handle(Node $node): null|Node;

    /**
     * @param  TNodeType  $node
     */
    public function shouldHandle(Node $node): bool;
}
