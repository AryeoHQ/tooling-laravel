<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use ReflectionClass;
use Tooling\Composer\ClassMap\Collectors\Contracts\Collector;
use Tooling\Composer\ClassMap\Collectors\Provides\Fakeable;

class ChildCollector implements Collector
{
    use Fakeable;

    public function collects(ReflectionClass $class, array $reflections): bool
    {
        $parent = $class->getParentClass();

        return $parent !== false && data_has($reflections, $parent->getName());
    }
}
