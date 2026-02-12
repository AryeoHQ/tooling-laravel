<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Enums;

use PhpParser\Node;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\Analyser\Scope;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<EnumCase>
 */
#[NodeType(EnumCase::class)]
final class CaseMustBePascal extends Rule
{
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return ! $this->isPascalCase($node);
    }

    /**
     * @param  EnumCase  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'Enum case must be `PascalCase`.',
            line: $node->name->getStartLine(),
            identifier: 'enums.caseMustBePascal'
        );
    }

    private function isPascalCase(EnumCase $node): bool
    {
        return preg_match('/^([A-Z][a-z0-9]*)+$/', $node->name->toString()) === 1;
    }
}
