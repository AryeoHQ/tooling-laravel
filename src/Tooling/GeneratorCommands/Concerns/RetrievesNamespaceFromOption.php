<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

use Illuminate\Support\Stringable;
use Symfony\Component\Console\Input\InputOption;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 */
trait RetrievesNamespaceFromOption
{
    protected function namespaceFromOption(): null|Stringable
    {
        $provided = $this->option('namespace');

        if (! $provided) {
            return null;
        }

        $provided = str($provided)->start('\\')->rtrim('\\');

        if (! $this->isValidNamespace($provided)) {
            $this->components->warn("Namespace [{$provided}] is not configured for this project.");

            return null;
        }

        return $provided;
    }

    /** @return array<int, InputOption> */
    protected function getNamespaceInputOptions(): array
    {
        return [
            new InputOption('namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace'),
        ];
    }
}
