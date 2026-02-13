<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class RuleMustExtendRule extends Rule
{
    private readonly ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

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

        if (! $this->inherits($node, \PHPStan\Rules\Rule::class, $this->reflectionProvider)) {
            return false;
        }

        return $this->doesNotInherit($node, Rule::class, $this->reflectionProvider);
    }

    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: sprintf('PHPStan rule must extend %s.', Rule::class),
            line: $node->getStartLine(),
            identifier: 'phpstan.ruleMustExtendRule'
        );
    }
}
