<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules;

use PhpParser\Node;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\Rector\AbstractRector;
use ReflectionClass;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Tooling\Rector\Rules\Definitions\Attributes\Definition;
use Tooling\Rector\Rules\Definitions\Attributes\Exceptions\DefinitionMissing;
use Tooling\Rector\Rules\Provides\EnsuresInterfaces;
use Tooling\Rector\Rules\Provides\EnsuresTraits;
use Tooling\Rector\Rules\Provides\ParsesNodes;
use Tooling\Rector\Rules\Provides\ValidatesAttributes;
use Tooling\Rector\Rules\Provides\ValidatesInheritance;
use Tooling\Rector\Rules\Samples\Attributes\Exceptions\SampleMissing;
use Tooling\Rector\Rules\Samples\Attributes\Sample;
use Tooling\Rules\Attributes\Exceptions\NodeTypeMissing;
use Tooling\Rules\Attributes\NodeType;
use Tooling\Rules\Provides\DefinesNodeTypes;

/**
 * @implements Contracts\Rule<Node>
 */
abstract class Rule extends AbstractRector implements Contracts\Rule
{
    use DefinesNodeTypes;
    use EnsuresInterfaces;
    use EnsuresTraits;
    use ParsesNodes;
    use ValidatesAttributes;
    use ValidatesInheritance;

    protected UseNodesToAddCollector $useNodesToAddCollector;

    final protected null|Sample $sample {
        get => collect(new ReflectionClass($this)->getAttributes(Sample::class))->first()?->newInstance()->for($this);
    }

    final protected null|Definition $definition {
        get => collect(new ReflectionClass($this)->getAttributes(Definition::class))->first()?->newInstance();
    }

    public function __construct(UseNodesToAddCollector $useNodesToAddCollector)
    {
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }

    public function shouldHandle(\PhpParser\Node $node): bool
    {
        return true;
    }

    final protected function shouldRefactor(\PhpParser\Node $node): bool
    {
        return $this->nodeTypes->filter(fn (NodeType $allowed) => $node instanceof $allowed->class)->isNotEmpty()
            && $this->shouldHandle($node);
    }

    final public function refactor(\PhpParser\Node $node): null|\PhpParser\Node
    {
        return $this->shouldRefactor($node) ? $this->handle($node) : null;
    }

    final public function getNodeTypes(): array
    {
        throw_unless($this->nodeTypes->isNotEmpty(), NodeTypeMissing::class, $this);

        return $this->nodeTypes->map->class->all();
    }

    final public function getRuleDefinition(): RuleDefinition
    {
        throw_unless($this->definition, DefinitionMissing::class, $this);
        throw_unless($this->sample, SampleMissing::class, $this);

        return $this->definition->toRuleDefinition(
            $this->sample->toCodeSample(),
        );
    }
}
