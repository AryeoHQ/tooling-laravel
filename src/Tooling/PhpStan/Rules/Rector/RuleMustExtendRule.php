<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Rector\Rector\AbstractRector;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rector;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class RuleMustExtendRule extends Rule
{
    /**
     * @param  Class_  $node
     */
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        if ($node->isAbstract()) {
            return false;
        }

        if ($node->isAnonymous()) {
            return false;
        }

        if (! $this->inherits($node, AbstractRector::class)) {
            return false;
        }

        return $this->doesNotInherit($node, Rector\Rules\Rule::class);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: sprintf('Rector rule must extend %s.', Rector\Rules\Rule::class),
            line: $node->getStartLine(),
            identifier: 'rector.ruleMustExtendRule'
        );
    }
}
