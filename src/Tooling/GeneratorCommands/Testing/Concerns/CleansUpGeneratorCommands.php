<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Testing\Concerns;

use Illuminate\Support\Collection;
use ReflectionProperty;
use Tooling\Composer\Composer;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\References\GenericClass;
use Tooling\GeneratorCommands\References\GenericTrait;

/**
 * @mixin \Tests\TestCase
 */
trait CleansUpGeneratorCommands
{
    protected function setUpCleansUpGeneratorCommands(): void
    {
        $this->deleteGeneratedFiles();
    }

    protected function tearDownCleansUpGeneratorCommands(): void
    {
        $this->deleteGeneratedFiles();
        $this->pruneEmptyDirectories();
    }

    private function deleteGeneratedFiles(): void
    {
        $this->generatedFilePaths()
            ->filter(fn (string $filePath) => $this->app['files']->exists($filePath))
            ->each(function (string $filePath) {
                $this->app['files']->delete($filePath);
            });

        $this->supplementalFilePaths()
            ->filter(fn (string $filePath) => $this->app['files']->exists($filePath))
            ->each(function (string $filePath) {
                $this->app['files']->delete($filePath);
            });
    }

    private function pruneEmptyDirectories(): void
    {
        $sourceRoots = resolve(Composer::class)->psr4SourceDirectories();

        $directories = $this->referenceProperties()
            ->map(fn (Reference $reference) => $reference->directory->toString());

        if (property_exists($this, 'files')) {
            /** @var array<array-key, string> $files */
            $files = $this->files;

            $directories = $directories->merge(
                collect($files)->map(fn (string $pattern) => dirname($pattern))
            );
        }

        $directories->unique()->each(function (string $directory) use ($sourceRoots) {
            $boundary = $sourceRoots
                ->sortByDesc(fn (string $root) => strlen($root))
                ->first(fn (string $root) => str_starts_with($directory, $root.'/'));

            if ($boundary !== null) {
                $this->deleteEmptyDirectoriesUpTo(from: $directory, boundary: $boundary);
            }
        });
    }

    private function deleteEmptyDirectoriesUpTo(string $from, string $boundary): void
    {
        if (! str_starts_with($from, $boundary.'/')) {
            return;
        }

        $files = $this->app['files'];
        $segments = explode('/', substr($from, strlen($boundary) + 1));

        collect(range(count($segments), 1))
            ->map(fn (int $depth) => $boundary.'/'.implode('/', array_slice($segments, 0, $depth)))
            ->each(function (string $directory) use ($files) {
                if ($files->isDirectory($directory) && $files->isEmptyDirectory($directory)) {
                    $files->deleteDirectory($directory);
                }
            });
    }

    /** @return Collection<int, string> */
    private function generatedFilePaths(): Collection
    {
        return $this->referenceProperties()
            ->flatMap(function (Reference $reference) {
                $paths = [$reference->filePath->toString()];

                if ($reference instanceof GenericClass || $reference instanceof GenericTrait) {
                    $paths[] = $reference->test->filePath->toString();
                }

                return $paths;
            })
            ->unique();
    }

    /** @return Collection<int, Reference> */
    private function referenceProperties(): Collection
    {
        return collect((new \ReflectionClass($this))->getProperties())
            ->filter(fn (ReflectionProperty $property) => $this->isReferenceProperty($property))
            ->map(fn (ReflectionProperty $property) => $this->{$property->getName()});
    }

    private function isReferenceProperty(ReflectionProperty $property): bool
    {
        $type = $property->getType();

        if (! $type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return false;
        }

        return is_a($type->getName(), Reference::class, true);
    }

    /** @return Collection<int, string> */
    private function supplementalFilePaths(): Collection
    {
        if (! property_exists($this, 'files')) {
            return collect();
        }

        /** @var array<array-key, string> $files */
        $files = $this->files;

        return collect($files)
            ->flatMap(function (string $pattern) {
                /** @var array<int, string> */
                return $this->app['files']->glob($pattern);
            })
            ->filter(fn (string $path) => $this->app['files']->isFile($path));
    }
}
