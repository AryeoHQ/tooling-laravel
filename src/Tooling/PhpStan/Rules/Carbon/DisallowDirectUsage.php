<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\Carbon;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Node\Expr>
 */
#[NodeType(Node\Expr::class)]
final class DisallowDirectUsage extends Rule
{
    /** @var string[] */
    public const DISALLOWED = [
        Carbon::class,
        CarbonImmutable::class,
        \Illuminate\Support\Carbon::class,
    ];

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        if (! ($node instanceof New_ || $node instanceof StaticCall || $node instanceof ClassConstFetch)) {
            return false;
        }

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

    /**
     * @param  Node\Expr  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'Direct use of Carbon is disallowed; use the `Date` facade instead, e.g. `Date::now()`.',
            line: $node->getStartLine(),
            identifier: 'carbon.disallowDirectUsage'
        );
    }

    private function findClassName(Node\Expr $node, Scope $scope): null|string
    {
        if (($node instanceof New_ || $node instanceof StaticCall || $node instanceof ClassConstFetch)
            && $node->class instanceof Node\Name) {
            return $scope->resolveName($node->class);
        }

        return null;
    }
}
