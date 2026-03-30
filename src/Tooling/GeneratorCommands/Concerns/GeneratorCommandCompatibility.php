<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 * @mixin \Tooling\GeneratorCommands\Contracts\GeneratesFile
 */
trait GeneratorCommandCompatibility
{
    public function getStub(): string
    {
        return $this->stub;
    }

    protected function getNameInput(): string
    {
        return $this->nameInput->toString();
    }

    protected function rootNamespace()
    {
        return $this->reference->namespace->after('\\')->toString();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->rootNamespace();
    }

    protected function getPath($name): string
    {
        return $this->reference->filePath->toString();
    }
}
