<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\Attributes\Test;
use Tooling\Rector\Rules\Definitions\Attributes\Definition;
use Tooling\Rector\Rules\Samples\Attributes\Sample;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[Definition('Replace test-prefixed methods with the #[Test] attribute')]
#[NodeType(Class_::class)]
#[Sample('tooling.rector.rules.samples')]
final class ReplaceTestFunctionPrefixWithAttribute extends Rule
{
    private const TEST_CASE = 'PHPUnit\\Framework\\TestCase';

    public function shouldHandle(Node $node): bool
    {
        return $this->inherits($node, self::TEST_CASE);
    }

    public function handle(Node $node): null|Node
    {
        $hasChanged = false;

        foreach ($node->getMethods() as $method) {
            if (! $method->isPublic() || $method->isStatic()) {
                continue;
            }

            $currentName = str($method->name->toString());

            if ($currentName->doesntStartWith('test')) {
                continue;
            }

            $newName = $currentName->chopStart(['test_', 'test'])->lcfirst();

            if ($newName->isNotEmpty()) {
                $method->name = new Identifier($newName->toString());
                $hasChanged = true;
            }

            if ($this->doesNotHaveAttribute($method, Test::class)) {
                $this->addAttribute($method, Test::class);
                $hasChanged = true;
            }
        }

        return $hasChanged ? $node : null;
    }
}
