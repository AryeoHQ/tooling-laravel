<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\GeneratorCommands;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Tooling\GeneratorCommands\Concerns\CreatesColocatedTests;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class CreatesColocatedTestsMustImplementGeneratesFile extends Rule
{
    /**
     * @param  Class_  $node
     */
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, CreatesColocatedTests::class)
            && $this->doesNotInherit($node, GeneratesFile::class);
    }

    /**
     * @param  Class_  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'CreatesColocatedTests must implement GeneratesFile.',
            line: $node->getStartLine(),
            identifier: 'tooling.createsColocatedTestsMustImplementGeneratesFile',
        );
    }
}
