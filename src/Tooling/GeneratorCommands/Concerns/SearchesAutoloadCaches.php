<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

use Illuminate\Support\Collection;
use Tooling\Composer\ClassMap\Cache;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 */
trait SearchesAutoloadCaches
{
    use SearchesNamespaces;

    /** @var Collection<int, string> */
    protected Collection $searchableClasses {
        get => $this->searchableClasses ??= $this->discoverSearchableClasses();
    }

    /** @return class-string<\Tooling\Composer\ClassMap\Collectors\Contracts\Collector> */
    abstract protected function collector(): string;

    /** @return array<array-key, string> */
    protected function getClassSearchResults(string $search = ''): array
    {
        return $this->searchableClasses
            ->filter(fn (string $class) => str($class)->lower()->is('*'.str($search)->lower()->toString().'*'))
            ->values()
            ->toArray();
    }

    /** @return Collection<int, string> */
    protected function discoverSearchableClasses(): Collection
    {
        $classes = collect(resolve(Cache::class)->get($this->collector()) ?? []);

        return method_exists($this, 'filterSearchableClasses')
            ? $this->filterSearchableClasses($classes)->values()
            : $classes;
    }
}
