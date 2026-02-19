<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Tooling\Rector\Rules\Definitions\Attributes\Definition;
use Tooling\Rector\Rules\Samples\Attributes\Sample;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[Definition('Add interface by parent class')]
#[NodeType(Class_::class)]
#[Sample('tooling.rector.rules.samples')]
final class AddInterfaceByClass extends Rule implements ConfigurableRectorInterface
{
    /** @var array<class-string, class-string> */
    private array $interfaceByClass = [];

    public function handle(Node $node): null|Node
    {
        $hasChanged = false;

        foreach ($this->interfaceByClass as $className => $interfaceName) {
            if (! $this->inherits($node, $className)) {
                continue;
            }

            if ($this->inherits($node, $interfaceName)) {
                continue;
            }

            $this->addInterface($node, $interfaceName);
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
        $this->interfaceByClass = $configuration;
    }
}
