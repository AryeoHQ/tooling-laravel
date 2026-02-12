<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Tooling\Rector\Rules\Definitions\Attributes\Definition;
use Tooling\Rector\Rules\Samples\Attributes\Sample;
use Tooling\Rules\Attributes\NodeType;

/**
 * @api used in rector-doctrine
 *
 * @see \Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\AddInterfaceByTraitRectorTest
 */
#[Definition('Add interface by used trait')]
#[NodeType(Class_::class)]
#[NodeType(Enum_::class)]
#[Sample('tooling.rector.rules.samples')]
final class AddInterfaceByTrait extends Rule implements ConfigurableRectorInterface
{
    /** @var array<class-string, class-string> */
    private array $interfaceByTrait = [];

    public function handle(Node $node): null|Node
    {
        if (! $node instanceof Class_ && ! $node instanceof Enum_) {
            return null;
        }

        $hasChanged = false;

        foreach ($this->interfaceByTrait as $traitName => $interfaceName) {
            if (! $this->inherits($node, $traitName)) {
                continue;
            }

            if ($this->inherits($node, $interfaceName)) {
                continue;
            }

            $this->ensureInterfaceIsImplemented($node, $interfaceName);
            $hasChanged = true;
        }

        return $hasChanged ? $node : null;
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
