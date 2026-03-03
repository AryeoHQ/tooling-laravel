<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Tooling\GeneratorCommands\Testing\Concerns\ReferenceTestCases;
use Tooling\GeneratorCommands\Testing\Contracts\TestsReference;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class TestsReferenceMustHaveReferenceTestCases extends Rule
{
    /**
     * @param  Class_  $node
     */
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, TestsReference::class)
            && $this->doesNotInherit($node, ReferenceTestCases::class);
    }

    /**
     * @param  Class_  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'TestsReference must use ReferenceTestCases.',
            line: $node->getStartLine(),
            identifier: 'tooling.referenceTestCases',
        );
    }
}
