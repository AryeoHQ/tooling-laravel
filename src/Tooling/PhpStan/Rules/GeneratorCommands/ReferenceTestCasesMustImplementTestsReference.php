<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\GeneratorCommands;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Tooling\GeneratorCommands\References\ReferenceTestCases;
use Tooling\GeneratorCommands\Testing\Contracts\TestsReference;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class ReferenceTestCasesMustImplementTestsReference extends Rule
{
    /**
     * @param  Class_  $node
     */
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, ReferenceTestCases::class)
            && $this->doesNotInherit($node, TestsReference::class);
    }

    /**
     * @param  Class_  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'ReferenceTestCases must implement TestsReference.',
            line: $node->getStartLine(),
            identifier: 'tooling.referenceTestCasesMustImplementTestsReference',
        );
    }
}
