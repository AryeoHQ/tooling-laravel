<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Composer\Util\Filesystem;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tooling\Composer\Testing\ClassMapSourceFake;

#[Singleton]
class ClassMapSource
{
    /**
     * @param  \Illuminate\Support\Collection<int, string>  $excluded
     * @return Collection<class-string, non-empty-string>
     */
    public function createMap(string $directory, Collection $excluded = new Collection, string $basePath = ''): Collection
    {
        return collect(
            tap(new ClassMapGenerator)->scanPaths(
                $directory,
                $this->exclusionPattern($excluded, $basePath)
            )->getClassMap()->getMap()
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $excluded
     * @return null|non-empty-string
     */
    protected function exclusionPattern(Collection $excluded, string $basePath): null|string
    {
        // Same path and wildcard rules as Composer's AutoloadGenerator.
        $patterns = $excluded->map(fn (string $path): string => str(preg_quote(new Filesystem()->normalizePath($basePath.'/'.$path)))
            ->swap(['\\*\\*' => '.+?', '\\*' => '[^/]+?'])
            ->append('($|/)')
            ->toString());

        return match ($patterns->isEmpty()) {
            true => null,
            false => Str::wrap($patterns->implode('|'), '{(', ')}'),
        };
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
