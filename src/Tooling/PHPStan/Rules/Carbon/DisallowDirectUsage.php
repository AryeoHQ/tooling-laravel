<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Rules\Carbon;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\Expr>
 */
final class DisallowDirectUsage implements Rule
{
    /** @var string[] */
    private const DISALLOWED = [
        'Carbon',
        'CarbonImmutable',
        'Carbon\\Carbon',
        'Carbon\\CarbonImmutable',
        'Illuminate\\Support\\Carbon',
        'Illuminate\\Support\\CarbonImmutable',
    ];

    public function getNodeType(): string
    {
        return Node\Expr::class;
    }

    /**
     * @param  Node\Expr  $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        return $this->passes($node, $scope) ? [] : $this->buildError($node);
    }

    private function passes(Node\Expr $node, Scope $scope): bool
    {
        return ! $this->violated($node, $scope);
    }

    private function violated(Node\Expr $node, Scope $scope): bool
    {
        // Only process the specific node types we care about
        if (! ($node instanceof New_ || $node instanceof StaticCall || $node instanceof ClassConstFetch)) {
            return false;
        }

        // Allow ::class usage as it's just getting the class name string, not using Carbon
        if ($node instanceof ClassConstFetch && $node->name instanceof Node\Identifier && $node->name->name === 'class') {
            return false;
        }

        $class = $this->findClassName($node, $scope);
        if ($class === null) {
            return false;
        }

        return collect(self::DISALLOWED)->contains(
            fn ($bad) => strcasecmp(ltrim($bad, '\\'), ltrim($class, '\\')) === 0
        );
    }

    private function findClassName(Node\Expr $node, Scope $scope): null|string
    {
        if ($node instanceof New_ && $node->class instanceof Node\Name) {
            return $scope->resolveName($node->class);
        }
        if ($node instanceof StaticCall && $node->class instanceof Node\Name) {
            return $scope->resolveName($node->class);
        }
        if ($node instanceof ClassConstFetch && $node->class instanceof Node\Name) {
            return $scope->resolveName($node->class);
        }

        return null;
    }

    /**
     * @return array<int, IdentifierRuleError>
     */
    private function buildError(Node\Expr $node): array
    {
        return [
            RuleErrorBuilder::message('Direct use of Carbon is disallowed; use the `Date` facade instead, e.g. `Date::now()`.')
                ->identifier('carbon.disallowDirectUsage')
                ->build(),
        ];
    }
}
