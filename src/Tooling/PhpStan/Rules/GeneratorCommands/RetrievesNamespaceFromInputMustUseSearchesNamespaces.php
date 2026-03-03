<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\GeneratorCommands;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Tooling\GeneratorCommands\Concerns\RetrievesNamespaceFromInput;
use Tooling\GeneratorCommands\Concerns\SearchesNamespaces;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class RetrievesNamespaceFromInputMustUseSearchesNamespaces extends Rule
{
    /**
     * @param  Class_  $node
     */
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, RetrievesNamespaceFromInput::class)
            && $this->doesNotInherit($node, SearchesNamespaces::class);
    }

    /**
     * @param  Class_  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'RetrievesNamespaceFromInput must use SearchesNamespaces.',
            line: $node->getStartLine(),
            identifier: 'tooling.retrievesNamespaceFromInputMustUseSearchesNamespaces',
        );
    }
}
