<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Concerns;

use Illuminate\Support\Collection;
use Tooling\Composer\Composer;

/**
 * @mixin \Illuminate\Console\GeneratorCommand
 */
trait SearchesClasses
{
    use SearchesNamespaces;

    /** @var Collection<int, string> */
    protected Collection $searchableClasses {
        get => $this->searchableClasses ??= $this->discoverSearchableClasses();
    }

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
        $composer = resolve(Composer::class);

        $composer->optimizeClassMap();

        $classes = $composer->classMap
            ->keys()
            ->filter(fn (string $class) => $this->availableNamespaces->keys()->contains(
                fn (string $namespace) => str_starts_with('\\'.$class, $namespace)
            ));

        return method_exists($this, 'filterSearchableClasses') // @phpstan-ignore function.alreadyNarrowedType
            ? $this->filterSearchableClasses($classes)->values()
            : $classes->values();
    }
}
