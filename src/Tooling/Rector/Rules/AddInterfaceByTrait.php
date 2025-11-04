<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @api used in rector-doctrine
 *
 * @see \Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\AddInterfaceByTraitRectorTest
 */
final class AddInterfaceByTrait extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private array $interfaceByTrait = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add interface by used trait', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    use SomeTrait;
}
CODE_SAMPLE
            , <<<'CODE_SAMPLE'
class SomeClass implements SomeInterface
{
    use SomeTrait;
}
CODE_SAMPLE
            , ['SomeTrait' => 'SomeInterface'])]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Enum_::class];
    }

    public function refactor(Node $node): null|Node
    {
        if (! $node instanceof Class_ && ! $node instanceof Enum_) {
            return null;
        }

        $hasChanged = false;

        foreach ($this->interfaceByTrait as $traitName => $interfaceName) {
            if (! $this->usesTrait($node, $traitName)) {
                continue;
            }

            if ($this->implementsInterface($node, $interfaceName)) {
                continue;
            }

            $node->implements[] = new FullyQualified($interfaceName);
            $hasChanged = true;
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    /**
     * @param  Class_|Enum_  $node
     */
    private function implementsInterface(Node $node, string $interfaceName): bool
    {
        if (! $node instanceof Class_ && ! $node instanceof Enum_) {
            return false;
        }

        foreach ($node->implements as $implement) {
            $implementName = $this->getName($implement);
            if ($implementName === $interfaceName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Class_|Enum_  $node
     */
    private function usesTrait(Node $node, string $traitName): bool
    {
        foreach ($node->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitUseName = $this->getName($trait);
                if ($traitUseName === $traitName) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  mixed[]  $configuration
     */
    public function configure(array $configuration): void
    {
        tap(collect($configuration), function (Collection $collection) {
            $collection->keys()->ensure('string');
            $collection->ensure('string');
        });
        $this->interfaceByTrait = $configuration;
    }
}
