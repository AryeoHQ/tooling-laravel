<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors\Provides;

use Tooling\Composer\ClassMap\Cache;

trait Fakeable
{
    /**
     * @param  array<array-key, string>  $classes
     * @return array<array-key, string>
     */
    public static function fake(array $classes = []): array
    {
        return Cache::fake()->provide([static::class => $classes])->get(static::class);
    }
}
