<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

use Illuminate\Support\Collection;
use Tooling\Composer\Composer;
use Tooling\Composer\Packages\Psr4Mapping;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 */
trait SearchesNamespaces
{
    /** @var Collection<string, string> */
    protected Collection $availableNamespaces {
        get => $this->availableNamespaces ??= resolve(Composer::class)->currentAsPackage->psr4Mappings
            ->groupBy(fn (Psr4Mapping $mapping): string => $mapping->prefix->toString())
            ->map(fn (Collection $mappings): string => $mappings->first()->path->toString())
            ->sortKeys();
    }
}
