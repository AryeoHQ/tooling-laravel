<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors;

use Illuminate\Support\Collection;
use Tooling\Composer\ClassMap\Collectors\Contracts\Collector;
use Tooling\Composer\ClassMap\Collectors\Provides\Fakeable;

class Untested implements Collector
{
    use Fakeable;

    /** @return \Illuminate\Support\Collection<int, class-string> */
    public function collect(Collection $classes): Collection
    {
        return $classes->reject(
            fn (string $class) => str_ends_with($class, 'Test') || str_ends_with($class, 'TestCases')
        )->reject(
            fn (string $class) => $classes->contains($class.'Test')
        )->values();
    }
}
