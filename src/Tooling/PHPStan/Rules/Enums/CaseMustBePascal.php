<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Rules\Enums;

use PhpParser\Node;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<EnumCase>
 */
final class CaseMustBePascal implements Rule
{
    public function getNodeType(): string
    {
        return EnumCase::class;
    }

    /**
     * @param  EnumCase  $node
     *
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        return $this->passes($node, $scope) ? [] : $this->buildError($node);
    }

    private function passes(EnumCase $node, Scope $scope): bool
    {
        return ! $this->violated($node, $scope);
    }

    private function violated(EnumCase $node, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();

        if ($classReflection === null) {
            return false;
        }

        // Ensure that the scope is an enum.
        if (! $classReflection->isEnum()) {
            return false;
        }

        return $this->isNotPascalCase($node);
    }

    private function isPascalCase(EnumCase $node): bool
    {
        return preg_match('/^([A-Z][a-z0-9]+)+$/', $node->name->toString()) === 1;
    }

    private function isNotPascalCase(EnumCase $node): bool
    {
        return ! $this->isPascalCase($node);
    }

    /**
     * @return array<int, IdentifierRuleError>
     */
    private function buildError(EnumCase $node): array
    {
        return [
            RuleErrorBuilder::message('Enum case must be `PascalCase`.')
                ->identifier('enums.caseMustBePascal')
                ->line($node->name->getStartLine())
                ->build(),
        ];
    }
}
