<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\GeneratorCommands;

use Illuminate\Console\GeneratorCommand;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class GeneratesFileMustExtendGeneratorCommand extends Rule
{
    /**
     * @param  Class_  $node
     */
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return $this->inherits($node, GeneratesFile::class)
            && $this->doesNotInherit($node, GeneratorCommand::class);
    }

    /**
     * @param  Class_  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'GeneratesFile must extend GeneratorCommand.',
            line: $node->getStartLine(),
            identifier: 'tooling.generatesFileMustExtendGeneratorCommand',
        );
    }
}
