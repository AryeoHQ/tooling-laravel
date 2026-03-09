<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Testing\Concerns;

use Illuminate\Support\Collection;
use ReflectionProperty;
use Tooling\GeneratorCommands\References\Contracts\Reference;

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
        $basePath = $this->app->basePath();

        $directories = $this->referenceProperties()
            ->map(fn (Reference $reference) => $reference->directoryPath->toString());

        if (property_exists($this, 'files')) {
            /** @var array<array-key, string> $files */
            $files = $this->files;

            $directories = $directories->merge(
                collect($files)->map(fn (string $pattern) => dirname($pattern))
            );
        }

        $directories
            ->unique()
            ->each(function (string $directory) use ($basePath) {
                while ($directory !== $basePath && str_starts_with($directory, $basePath)) {
                    if (! $this->app['files']->isDirectory($directory) || ! $this->app['files']->isEmptyDirectory($directory)) {
                        break;
                    }

                    $this->app['files']->deleteDirectory($directory);
                    $directory = dirname($directory);
                }
            });
    }

    /** @return Collection<int, string> */
    private function generatedFilePaths(): Collection
    {
        return $this->referenceProperties()
            ->flatMap(fn (Reference $reference) => [
                $reference->filePath->toString(),
                $reference->test->filePath->toString(),
            ])
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
