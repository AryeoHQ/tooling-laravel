<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

use Illuminate\Support\Stringable;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 */
trait RetrievesNamespace
{
    use RetrievesNamespaceFromArgument;
    use RetrievesNamespaceFromOption;
    use SearchesNamespaces;

    protected Stringable $baseNamespace;

    protected Stringable $baseDirectory;

    protected function retrieveNamespace(): Stringable
    {
        return $this->namespaceFromOption() ?? $this->namespaceFromArgument() ?? $this->namespaceFromPrompt();
    }

    protected function resolveNamespace(): void
    {
        $this->baseNamespace = $this->retrieveNamespace()->finish('\\');
        $this->baseDirectory = str($this->availableNamespaces->get($this->baseNamespace->toString()));
    }

    protected function namespaceFromPrompt(): Stringable
    {
        $result = \Laravel\Prompts\select(
            label: "Which namespace should the {$this->type} be created in?",
            options: $this->availableNamespaces->keys()->map(fn (string $ns) => rtrim($ns, '\\'))->all(),
            required: true,
        );

        return str($result);
    }

    protected function isValidNamespace(Stringable $namespace): bool
    {
        return $this->availableNamespaces->keys()->contains(
            $namespace->finish('\\')
        );
    }
}
