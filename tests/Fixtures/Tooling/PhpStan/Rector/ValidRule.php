<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Tooling\Rector\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

#[NodeType(Class_::class)]
final class ValidRule extends Rule
{
    public function handle(Node $node): null|Node
    {
        return null;
    }
}
