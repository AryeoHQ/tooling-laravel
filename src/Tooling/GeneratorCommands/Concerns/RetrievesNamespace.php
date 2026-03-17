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
        $this->baseDirectory = str($this->resolveBaseDirectory($this->baseNamespace));
    }

    private function resolveBaseDirectory(Stringable $namespace): null|string
    {
        $normalized = $namespace->toString();

        return $this->availableNamespaces
            ->sortKeysDesc()
            ->first(fn (string $dir, string $prefix) => str_starts_with($normalized, $prefix));
    }

    protected function namespaceFromPrompt(): Stringable
    {
        $result = \Laravel\Prompts\suggest(
            label: "Which namespace should the {$this->type} be created in?",
            options: $this->availableNamespaces->keys()->map(fn (string $ns) => rtrim($ns, '\\'))->values()->all(),
            required: true,
            validate: fn (string $value) => $this->isValidNamespace(str($value))
                ? null
                : "Namespace [{$value}] is not configured for this project.",
        );

        return str($result);
    }

    protected function isValidNamespace(Stringable $namespace): bool
    {
        $normalized = $namespace->finish('\\')->toString();

        return $this->availableNamespaces->keys()->contains(
            fn (string $prefix) => str_starts_with($normalized, $prefix)
        );
    }
}
