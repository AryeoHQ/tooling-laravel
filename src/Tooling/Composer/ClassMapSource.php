<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Support\Collection;
use Tooling\Composer\Testing\ClassMapSourceFake;

class ClassMapSource
{
    /**
     * @return Collection<class-string, non-empty-string>
     */
    public function createMap(string $directory): Collection
    {
        return collect(ClassMapGenerator::createMap($directory));
    }

    /**
     * @param  array<string, string>  $classMap
     */
    public static function fake(array $classMap = []): ClassMapSourceFake
    {
        $classMapSource = resolve(ClassMapSource::class);

        // ClassMapSource and Composer are tightly coupled as they are driven by the same `composer.json` file.
        // They must be faked together for consistency. Given that `Composer` is an object representation of
        // `composer.json` it's faked representation is the source of truth for the data that
        // `ClassMapSource` will represent. We let `Composer::fake()` own the responsibility
        // of defining a coherent world for a `composer.json` file.
        if (! $classMapSource instanceof ClassMapSourceFake) {
            Composer::fake();
        }

        /** @var \Tooling\Composer\Testing\ClassMapSourceFake $classMapSourceFake */
        $classMapSourceFake = resolve(ClassMapSource::class);

        return $classMapSourceFake->merge($classMap);
    }
}
