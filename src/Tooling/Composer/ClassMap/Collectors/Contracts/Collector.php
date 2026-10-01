<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors\Contracts;

use ReflectionClass;

interface Collector
{
    /**
     * @param  \ReflectionClass<*>  $class
     * @param  array<class-string, \ReflectionClass<*>>  $reflections  every class in the scan that loaded, keyed by name
     */
    public function collects(ReflectionClass $class, array $reflections): bool;

    /**
     * @param  array<array-key, string>  $classes
     * @return array<array-key, string>
     */
    public static function fake(array $classes = []): array;
}
