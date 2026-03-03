<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

use Illuminate\Support\Collection;
use Tooling\Composer\Composer;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 */
trait SearchesNamespaces
{
    /** @var Collection<string, string> */
    protected Collection $availableNamespaces {
        get => $this->availableNamespaces ??= collect([
            $this->laravel->getNamespace() => $this->laravel->basePath('app'),
        ])->merge((array) data_get(
            resolve(Composer::class)->currentAsPackage->autoload, 'psr-4', []
        ))->merge((array) data_get(
            resolve(Composer::class)->currentAsPackage->autoloadDev, 'psr-4', []
        ))->sortKeys();
    }
}
