<?php

declare(strict_types=1);

namespace Tooling\Composer\Testing;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Tooling\Composer\ClassMapSource;

final class ClassMapSourceFake extends ClassMapSource
{
    /** @var array<string, array<class-string, non-empty-string>> */
    private array $maps = [];

    public static function make(): static
    {
        return new self;
    }

    /**
     * @param  array<string, string>  $classMap
     */
    public function merge(array $classMap): static
    {
        foreach ($classMap as $class => $path) {
            $namespace = str($class)->beforeLast('\\');
            $className = str($class)->afterLast('\\');

            File::put($path, "<?php namespace {$namespace}; class {$className} {}");

            $directory = collect(array_keys($this->maps))
                ->first(fn (string $dir) => str_starts_with($path, $dir.'/'))
                ?? dirname($path);

            $this->maps[$directory][$class] = $path;
        }

        return $this;
    }

    /**
     * @return Collection<class-string, non-empty-string>
     */
    public function createMap(string $directory): Collection
    {
        return collect($this->maps[$directory] ?? []);
    }
}
