<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

use Illuminate\Support\Stringable;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 */
trait RetrievesNamespaceFromArgument
{
    protected function namespaceFromArgument(): null|Stringable
    {
        if (! $this->hasArgument('namespace')) {
            return null;
        }

        $provided = str((string) $this->argument('namespace')); // @phpstan-ignore larastan.console.undefinedArgument

        if ($provided->isEmpty()) {
            return null;
        }

        $provided = $provided->start('\\')->rtrim('\\');

        if (! $this->isValidNamespace($provided)) {
            $this->components->warn("Namespace [{$provided}] is not configured for this project.");

            return null;
        }

        return $provided;
    }

    /** @return array<int, InputArgument> */
    protected function getNamespaceInputArguments(): array
    {
        return [
            new InputArgument('namespace', InputArgument::REQUIRED, 'The root namespace'),
        ];
    }

    /**
     * @return array<string, \Closure(): string>
     */
    protected function getNamespacePromptForMissingArguments(): array
    {
        return ['namespace' => fn () => $this->namespaceFromPrompt()->toString()];
    }
}
