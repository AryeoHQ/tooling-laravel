<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Tooling\Composer\Packages\Package;
use Tooling\Composer\Testing\ManifestFake;

use function Illuminate\Filesystem\join_paths;

/**
 * @property-read null|\stdClass $phpstan
 * @property-read null|\stdClass $rector
 */
class Manifest
{
    /** @var array<string, mixed> */
    protected array $cache = [];

    private Filesystem $files;

    private Composer $composer;

    /** @var Collection<array-key, Package> */
    public Collection $packages {
        get => $this->cache[__PROPERTY__] ??= $this->composer->packages->concat([
            $this->composer->currentAsPackage,
        ]);
    }

    public string $manifestPath {
        get => join_paths($this->composer->vendorDirectory->toString(), $this->composer->selfAsPackage->name->toString(), 'cache/configurations.php');
    }

    public object $loaded {
        get => match ($this->hasCache(__PROPERTY__) && ! $this->isStale(__PROPERTY__)) {
            true => $this->cache(__PROPERTY__),
            false => with(__PROPERTY__, function (string $key) {
                when($this->isStale($key), fn () => $this->clearCache()->build());

                return $this->cache($key, (object) $this->files->getRequire($this->manifestPath), $this->files->lastModified($this->manifestPath));
            }),
        };
    }

    public function __construct(null|Filesystem $files = null, null|Composer $composer = null)
    {
        $this->files = $files ?? resolve(Filesystem::class);
        $this->composer = $composer ?? resolve(Composer::class);
    }

    public function build(): true
    {
        $this->write([
            'rector' => $this->collectRector()->toArray(),
            'phpstan' => $this->collectPhpStan()->toArray(),
        ]);

        return true;
    }

    /**
     * @return Collection<string, array<array-key, mixed>>
     */
    private function collectRector(): Collection
    {
        return $this->packages->map(
            fn (Package $package) => $this->extractRector($package)->toArray()
        )->filter()->reduce(function ($carry, $row) {
            foreach ($row as $key => $value) {
                $carry->put(
                    $key,
                    array_merge_recursive($carry->get($key, []), $this->files->getRequire($value))
                );
            }

            return $carry->unique();
        }, collect());
    }

    /**
     * @return Collection<array-key, string>
     */
    private function extractRector(Package $package): Collection
    {
        $configuration = (array) data_get($package, 'extra.tooling.rector');

        return collect($configuration)->filter(fn ($value): bool => is_string($value))->map(
            fn (string $path): string => match ($package->name === $this->composer->currentAsPackage->name) {
                true => join_paths($this->composer->baseDirectory->toString(), $path),
                false => join_paths($this->composer->baseDirectory->toString(), 'vendor', $package->name->toString(), $path)
            }
        )->filter(
            fn ($path) => $this->files->isFile($path)
        );
    }

    /**
     * @return Collection<string, array<array-key, string>>
     */
    private function collectPhpStan(): Collection
    {
        return $this->packages->map(
            fn (Package $package) => $this->extractPhpStan($package)->toArray()
        )->filter()->reduce(function ($carry, $row) {
            foreach ($row as $key => $value) {
                $carry->put(
                    $key,
                    array_merge($carry->get($key, []), [$value])
                );
            }

            return $carry->unique();
        }, collect());
    }

    /**
     * @return Collection<array-key, string>
     */
    private function extractPhpStan(Package $package): Collection
    {
        $configuration = (array) data_get($package, 'extra.tooling.phpstan');

        return collect($configuration)->filter(fn ($value): bool => is_string($value))->map(
            fn (string $path): string => match ($package->name === $this->composer->currentAsPackage->name) {
                true => join_paths($this->composer->baseDirectory->toString(), $path),
                false => join_paths($this->composer->baseDirectory->toString(), 'vendor', $package->name->toString(), $path)
            }
        )->filter(
            fn ($path) => $this->files->isFile($path)
        );
    }

    /**
     * @param  array<array-key, mixed>  $manifest
     */
    private function write(array $manifest): void
    {
        $this->files->ensureDirectoryExists(dirname($this->manifestPath));
        $this->files->put($this->manifestPath, '<?php return '.var_export($manifest, true).';');

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->manifestPath, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->loaded, $key, $default);
    }

    public function __get(string $key)
    {
        return $this->get($key);
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

    protected function clearCache(): static
    {
        $this->cache = [];

        return $this;
    }

    protected function isStale(string $key): bool
    {
        if (! $this->files->exists($this->manifestPath)) {
            return true;
        }

        if ($this->hasCache($key.'::time') && $this->files->lastModified($this->manifestPath) !== $this->cacheTime($key)) {
            return true;
        }

        return $this->files->lastModified($this->composer->composerJsonPath)
            >= $this->files->lastModified($this->manifestPath);
    }

    public static function fake(): ManifestFake
    {
        $manifest = resolve(Manifest::class);

        if ($manifest instanceof ManifestFake) {
            return $manifest;
        }

        // Manifest depends on Composer for paths (vendorDirectory, selfAsPackage) and package data.
        // They must be faked together for consistency. We let Composer::fake() own the responsibility
        // of defining a coherent world that Manifest will operate within.
        Composer::fake();

        /** @var ManifestFake */
        return resolve(Manifest::class);
    }
}
