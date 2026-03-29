<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap;

use Illuminate\Filesystem\Filesystem;
use Tooling\Composer\Composer;
use Tooling\Composer\Testing\CacheFake;

use function Illuminate\Filesystem\join_paths;

class Cache
{
    /** @var array<string, mixed> */
    protected array $cache = [];

    private Filesystem $files;

    private Composer $composer;

    public string $cachePath {
        get => join_paths($this->composer->vendorDirectory->toString(), $this->composer->selfAsPackage->name->toString(), 'cache/classmap.php');
    }

    /** @var array<string, array<int, string>> */
    public array $loaded {
        get => match ($this->hasCache(__PROPERTY__) && ! $this->isStale(__PROPERTY__)) {
            true => $this->cache(__PROPERTY__),
            false => with(__PROPERTY__, function (string $key) {
                when($this->isStale($key), fn () => $this->clearCache()->build());

                return $this->cache($key, (array) $this->files->getRequire($this->cachePath), $this->files->lastModified($this->cachePath));
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
        $classes = $this->composer->sourcePsr4ClassMap->keys();

        $data = collect(iterator_to_array(app()->tagged('tooling.classmap.collectors')))->mapWithKeys(
            fn (Collectors\Contracts\Collector $collector) => [
                $collector::class => $collector->collect($classes)->all(),
            ])->all();

        $this->write($data);

        return true;
    }

    /** @return null|array<int, string> */
    public function get(string $key): null|array
    {
        return $this->loaded[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * @param  array<string, array<int, string>>  $data
     */
    private function write(array $data): void
    {
        $this->files->ensureDirectoryExists(dirname($this->cachePath));
        $this->files->put($this->cachePath, '<?php return '.var_export($data, true).';');

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->cachePath, true);
        }
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
        if (! $this->files->exists($this->cachePath)) {
            return true;
        }

        if ($this->hasCache($key.'::time') && $this->files->lastModified($this->cachePath) !== $this->cacheTime($key)) {
            return true;
        }

        return $this->composer->hasSourcePsr4ChangedSince(
            $this->files->lastModified($this->cachePath)
        );
    }

    public static function fake(): CacheFake
    {
        $cache = resolve(Cache::class);

        if ($cache instanceof CacheFake) {
            return $cache;
        }

        // Cache depends on Composer for paths (cachePath) and staleness checks (hasSourcePsr4ChangedSince).
        // They must be faked together for consistency. We let Composer::fake() own the responsibility
        // of defining a coherent world that Cache will operate within.
        Composer::fake();

        /** @var CacheFake */
        return resolve(Cache::class);
    }
}
