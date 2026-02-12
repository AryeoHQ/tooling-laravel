<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Rules;

use Illuminate\Support\Collection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Tooling\PHPStan\Rules\Provides\ValidatesAttributes;
use Tooling\PHPStan\Rules\Provides\ValidatesInheritance;
use Tooling\Rules\Attributes\Exceptions\NodeTypeMissing;
use Tooling\Rules\Provides\DefinesNodeTypes;

/**
 * @template TNodeType of Node
 *
 * @implements Contracts\Rule<TNodeType>
 */
abstract class Rule implements Contracts\Rule
{
    use DefinesNodeTypes;
    use ValidatesAttributes;
    use ValidatesInheritance;

    /** @var Collection<int, IdentifierRuleError> */
    protected Collection $errors;

    /**
     * {@inheritDoc}
     */
    public function getNodeType(): string
    {
        throw_unless($this->nodeTypes->isNotEmpty(), NodeTypeMissing::class, $this);

        return $this->nodeTypes->first()->class;
    }

    public function prepare(Node $node, Scope $scope): void {}

    public function shouldHandle(Node $node, Scope $scope): bool
    {
        return true;
    }

    protected function shouldProcessNode(Node $node, Scope $scope): bool
    {
        $nodeClass = $this->nodeTypes->first()->class;

        return $node instanceof $nodeClass && $this->shouldHandle($node, $scope);
    }

    /**
     * {@inheritDoc}
     */
    final public function processNode(Node $node, Scope $scope): array
    {
        $this->errors = collect();

        $this->prepare($node, $scope);

        if (! $this->shouldProcessNode($node, $scope)) {
            return [];
        }

        $this->handle($node, $scope);

        return $this->errors->all();
    }

    protected function error(string $message, int $line, string $identifier): void
    {
        $this->errors->push(
            RuleErrorBuilder::message($message)->line($line)->identifier($identifier)->build()
        );
    }
}
