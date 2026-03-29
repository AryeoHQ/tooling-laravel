<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Tooling\Composer\Packages\Package;
use Tooling\Composer\Packages\Packages;
use Tooling\Composer\Testing\ComposerFake;

use function Illuminate\Filesystem\join_paths;

class Composer
{
    /** @var array<string, mixed> */
    protected array $cache = [];

    private Filesystem $files;

    private ClassMapSource $classMapSource;

    public Stringable $vendorDirectory {
        get {
            return $this->cache[__PROPERTY__] ??= str(collect([dirname(__DIR__, 3).'/vendor', dirname(__DIR__, 6).'/vendor'])
                ->filter(fn (string $path): bool => $this->files->isDirectory($path))
                ->first());
        }
    }

    public Stringable $baseDirectory {
        get => $this->cache[__PROPERTY__] ??= $this->vendorDirectory->replace('/vendor', '');
    }

    public string $composerJsonPath {
        get => $this->cache[__PROPERTY__] ??= $this->baseDirectory->append('/composer.json')->toString();
    }

    public Packages $packages {
        get => $this->cache[__PROPERTY__] ??= Packages::make($this->vendorDirectory->toString(), $this->files);
    }

    public Package $currentAsPackage {
        get => $this->cache[__PROPERTY__] ??= new Package(
            json_decode($this->files->get($this->composerJsonPath))
        );
    }

    public Package $selfAsPackage {
        get => $this->cache[__PROPERTY__] ??= with(
            dirname(__DIR__, 3).'/composer.json',
            fn (string $path) => match ($this->files->exists($path)) {
                true => new Package(json_decode($this->files->get($path))),
                false => $this->currentAsPackage,
            }
        );
    }

    /** @var Collection<class-string, non-empty-string> */
    public Collection $sourcePsr4ClassMap {
        get => match ($this->hasCache(__PROPERTY__) && ! $this->hasSourcePsr4ChangedSince($this->cacheTime(__PROPERTY__))) {
            true => $this->cache(__PROPERTY__),
            false => with(__PROPERTY__, function (string $key) {
                return $this->cache($key, $this->sourcePsr4Directories()
                    ->flatMap(fn (string $dir): Collection => $this->classMapSource->createMap($dir)));
            }),
        };
    }

    public function __construct(null|Filesystem $files = null, null|ClassMapSource $classMapSource = null)
    {
        $this->files = $files ?? resolve(Filesystem::class);
        $this->classMapSource = $classMapSource ?? resolve(ClassMapSource::class);
    }

    public function hasSourcePsr4ChangedSince(int $timestamp): bool
    {
        return $this->sourcePsr4Directories()->contains(
            fn (string $path) => collect($this->files->allFiles($path))->map(
                fn (\Symfony\Component\Finder\SplFileInfo $file): string => $this->files->dirname($file->getPathname())
            )->unique()->push($path)->contains(
                fn (string $dir): bool => $this->files->lastModified($dir) >= $timestamp
            )
        );
    }

    public function vendorPath(string ...$path): string
    {
        return join_paths($this->vendorDirectory->toString(), ...$path);
    }

    /** @return Collection<int, string> */
    public function sourcePsr4Directories(): Collection
    {
        $basePath = $this->baseDirectory->toString();

        return $this->currentAsPackage->psr4Mappings->map(
            fn (\Tooling\Composer\Packages\Psr4Mapping $mapping): string => $mapping->path->toString()
        )->map(
            fn (string $relativePath): string => rtrim(join_paths($basePath, $relativePath), '/')
        )->filter(
            fn (string $path): bool => $this->files->exists($path) && $this->files->isDirectory($path)
        )->values();
    }

    public function artisan(): null|string
    {
        $base = $this->baseDirectory->toString();

        return collect(['/artisan', '/vendor/bin/testbench'])->map(
            fn (string $path): string => $base.$path
        )->filter(
            fn (string $path): bool => $this->files->exists($path)
        )->first();
    }

    protected function hasCache(string $key): bool
    {
        return array_key_exists($key, $this->cache);
    }

    /**
     * @template TValue
     *
     * @param  TValue  $value
     * @return TValue
     */
    protected function cache(string $key, mixed $value = null, null|int $time = null): mixed
    {
        if (func_num_args() > 1) {
            $this->cache[$key.'::time'] = $time ?? now()->timestamp;
            $this->cache[$key] = $value;
        }

        return $this->cache[$key];
    }

    protected function cacheTime(string $key): int
    {
        return $this->cache[$key.'::time'];
    }

    /**
     * @param  array<string, mixed>  $composerJson
     */
    public static function fake(array $composerJson = []): ComposerFake
    {
        $composer = resolve(Composer::class);

        if ($composer instanceof ComposerFake) {
            return $composer;
        }

        return ComposerFake::make($composerJson);
    }
}
