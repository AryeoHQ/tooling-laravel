<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PHPStan\PHPStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class InvalidRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return [
            RuleErrorBuilder::message('Example error')->build(),
        ];
    }
}
