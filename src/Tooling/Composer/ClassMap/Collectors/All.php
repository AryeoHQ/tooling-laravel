<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors;

use ReflectionClass;
use Tooling\Composer\ClassMap\Collectors\Contracts\Collector;
use Tooling\Composer\ClassMap\Collectors\Provides\Fakeable;

class All implements Collector
{
    use Fakeable;

    public function collects(ReflectionClass $class, array $reflections): bool
    {
        return true;
    }
}
