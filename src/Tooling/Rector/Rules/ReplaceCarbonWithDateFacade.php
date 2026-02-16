<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Tooling\PhpStan\Rules\Carbon\DisallowDirectUsage;
use Tooling\Rector\Rules\Definitions\Attributes\Definition;
use Tooling\Rector\Rules\Samples\Attributes\Sample;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Node>
 */
#[Definition('Replace direct Carbon usage with the Date facade')]
#[NodeType(StaticCall::class)]
#[NodeType(New_::class)]
#[Sample('tooling.rector.rules.samples')]
final class ReplaceCarbonWithDateFacade extends Rule
{
    private const DATE_FACADE = 'Illuminate\\Support\\Facades\\Date';

    public function shouldHandle(Node $node): bool
    {
        if (! $node instanceof StaticCall && ! $node instanceof New_) {
            return false;
        }

        if (! $node->class instanceof Node\Name) {
            return false;
        }

        return $this->isCarbonClass($node->class->toString());
    }

    public function handle(Node $node): null|Node
    {
        if ($node instanceof New_) {
            return $this->replaceNew($node);
        }

        if ($node instanceof StaticCall) {
            return $this->replaceStaticCall($node);
        }

        return null;
    }

    private function replaceNew(New_ $node): StaticCall
    {
        return new StaticCall(
            new FullyQualified(self::DATE_FACADE),
            'create',
            $node->args,
        );
    }

    private function replaceStaticCall(StaticCall $node): StaticCall
    {
        return new StaticCall(
            new FullyQualified(self::DATE_FACADE),
            $node->name,
            $node->args,
        );
    }

    private function isCarbonClass(string $className): bool
    {
        $normalized = ltrim($className, '\\');

        return collect(DisallowDirectUsage::DISALLOWED)->contains(
            fn (string $disallowed) => strcasecmp(ltrim($disallowed, '\\'), $normalized) === 0
        );
    }
}
