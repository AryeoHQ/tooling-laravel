<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

#[NodeType(Class_::class)]
final class ValidRule extends Rule
{
    public function handle(Node $node, Scope $scope): void {}
}
