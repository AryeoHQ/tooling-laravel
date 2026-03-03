<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

use Illuminate\Support\Stringable;

use function Laravel\Prompts\select;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 * @mixin \Tooling\GeneratorCommands\Concerns\SearchesNamespaces
 */
trait RetrievesNamespaceFromInput
{
    protected Stringable $baseNamespace;

    protected Stringable $baseDirectory;

    protected function promptForNamespace(): void
    {
        $namespace = $this->namespaceFromOption() ?? $this->namespaceFromPrompt();

        $this->baseNamespace = $namespace->finish('\\');
        $this->baseDirectory = str($this->availableNamespaces->get($this->baseNamespace->toString()));
    }

    private function namespaceFromOption(): null|Stringable
    {
        $provided = $this->option('namespace');

        if (! $provided) {
            return null;
        }

        if ($this->isValidNamespace($provided = str($provided))) {
            return $provided;
        }

        $this->components->warn("Namespace [{$provided}] is not configured for this project.");

        return null;
    }

    private function namespaceFromPrompt(): Stringable
    {
        $result = select(
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
