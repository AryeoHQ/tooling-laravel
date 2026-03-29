<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors\Contracts;

use Illuminate\Support\Collection;

interface Collector
{
    /**
     * @param  \Illuminate\Support\Collection<int, class-string>  $classes
     * @return \Illuminate\Support\Collection<int, class-string>
     */
    public function collect(Collection $classes): Collection;

    /**
     * @param  array<array-key, string>  $classes
     * @return array<array-key, string>
     */
    public static function fake(array $classes = []): array;
}
