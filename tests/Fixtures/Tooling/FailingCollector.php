<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use ReflectionClass;
use RuntimeException;
use Tooling\Composer\ClassMap\Collectors\Contracts\Collector;
use Tooling\Composer\ClassMap\Collectors\Provides\Fakeable;

class FailingCollector implements Collector
{
    use Fakeable;

    public function collects(ReflectionClass $class, array $reflections): bool
    {
        throw new RuntimeException('failure');
    }
}
