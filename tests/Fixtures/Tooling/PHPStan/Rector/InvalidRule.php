<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PHPStan\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class InvalidRule extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): null|Node
    {
        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Invalid rule', [
            new CodeSample('', ''),
        ]);
    }
}
