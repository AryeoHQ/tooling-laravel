<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Stringable;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Tooling\Composer\Packages\Package;
use Tooling\Composer\Packages\Packages;

use function Illuminate\Filesystem\join_paths;

class Composer
{
    /** @var array<string, mixed> */
    private array $cache = [];

    public string $vendorDirectory {
        get {
            return collect([__DIR__.'/../../../vendor', __DIR__.'/../../../../../../vendor'])->map(
                fn (string $path): string|bool => realpath($path)
            )->filter()->first(
                fn (string $path): bool => is_dir($path)
            );
        }
    }

    public Stringable $baseDirectory { get => $this->baseDirectory ??= str($this->vendorDirectory)->replace('/vendor', ''); }

    public SplFileInfo $composerJsonFile {
        get => new SplFileInfo(
            $this->baseDirectory->append('/composer.json')->toString(),
            '',
            $this->baseDirectory->toString()
        );
    }

    public SplFileInfo $classMapFile {
        get => new SplFileInfo(
            $this->vendorPath('composer', 'autoload_classmap.php'),
            '',
            $this->vendorPath('composer')
        );
    }

    /** @var Collection<array-key, mixed> */
    public Collection $classMap {
        get => $this->cache[__FUNCTION__] ??= collect((array) require $this->classMapFile->getRealPath());
    }

    public bool $isOptimized {
        get => $this->cache[__FUNCTION__] ??= str_contains(
            file_get_contents($this->classMapFile->getRealPath()),
            '$baseDir . '
        );
    }

    public Packages $packages { get => $this->packages ??= Packages::make($this->vendorDirectory); }

    public Package $currentAsPackage {
        get => $this->currentAsPackage ??= new Package(
            json_decode($this->composerJsonFile->getContents())
        );
    }

    public Package $selfAsPackage {
        get => $this->selfAsPackage ??= when(
            realpath(__DIR__.'/../../../composer.json'),
            fn (string $path): Package => new Package(
                json_decode(new SplFileInfo($path, '', basename($path))->getContents())
            )
        );
    }

    public bool $isClassMapStale {
        get {
            $sourceDirectories = $this->psr4SourceDirectories();

            if ($sourceDirectories->isEmpty()) {
                return false;
            }

            $classMapMTime = $this->classMapFile->getMTime();

            $hasNewerFiles = LazyCollection::make(Finder::create()->files()->name('*.php')->in($sourceDirectories->all()))
                ->contains(fn (SplFileInfo $file): bool => $file->getMTime() > $classMapMTime);

            if ($hasNewerFiles) {
                return true;
            }

            $hasDeletedFiles = $this->classMap
                ->filter(fn (mixed $filePath): bool => is_string($filePath))
                ->filter(fn (string $filePath): bool => $sourceDirectories->contains(
                    fn (string $directory): bool => str_starts_with($filePath, $directory)
                ))
                ->contains(fn (string $filePath): bool => ! file_exists($filePath));

            return $hasDeletedFiles;
        }
    }

    public function optimizeClassMap(): void
    {
        if ($this->isOptimized && ! $this->isClassMapStale) {
            return;
        }

        $result = Process::path($this->baseDirectory->toString())
            ->run('composer dump-autoload -o --no-scripts --no-interaction');

        if (! $result->successful()) {
            throw new RuntimeException('Failed to optimize classmap: '.$result->errorOutput());
        }

        $path = $this->classMapFile->getRealPath();

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }

        clearstatcache(true, $path);

        $this->cache = [];
    }

    public function vendorPath(string ...$path): string
    {
        return join_paths($this->vendorDirectory, ...$path);
    }

    /** @return Collection<int, non-empty-string> */
    public function psr4SourceDirectories(): Collection
    {
        $basePath = $this->baseDirectory->toString();

        return $this->currentAsPackage->psr4Mappings
            ->map(fn (\Tooling\Composer\Packages\Psr4Mapping $mapping): string => $mapping->path)
            ->map(fn (string $relativePath): string|false => realpath(join_paths($basePath, $relativePath)))
            ->filter(fn (string|false $path): bool => $path !== false && is_dir($path))
            ->values();
    }
}
