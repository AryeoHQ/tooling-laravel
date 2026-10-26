<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use RuntimeException;
use Tooling\Composer\ClassMap\Autoloaders\ThrowsOnMissing;
use Tooling\Composer\Composer;
use Tooling\Composer\Testing\CacheFake;

use function Illuminate\Filesystem\join_paths;

#[Singleton]
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
            false => match ($this->clearCache()->isStale(__PROPERTY__)) {
                true => $this->build(),
                false => $this->cache(__PROPERTY__, (array) $this->files->getRequire($this->cachePath), $this->files->lastModified($this->cachePath)),
            },
        };
    }

    public function __construct(null|Filesystem $files = null, null|Composer $composer = null)
    {
        $this->files = $files ?? resolve(Filesystem::class);
        $this->composer = $composer ?? resolve(Composer::class);
    }

    /** @return array<string, array<int, string>> */
    public function build(): array
    {
        return $this->classify($this->reflect($this->composer->sourcePsr4ClassMap->keys()))
            ->tap(fn (Collection $data) => rescue(fn () => $this->write($data)))
            ->pipe(fn (Collection $data) => $this->clearCache()->cache('loaded', $data->all()));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, class-string>  $classes
     * @return \Illuminate\Support\Collection<class-string, \ReflectionClass<object>>
     */
    private function reflect(Collection $classes): Collection
    {
        spl_autoload_register($autoloader = new ThrowsOnMissing);

        $reflections = $classes
            ->mapWithKeys(fn (string $class) => [$class => rescue(fn () => new ReflectionClass($class), report: false)])
            ->filter();

        spl_autoload_unregister($autoloader);

        return $reflections;
    }

    /**
     * @param  \Illuminate\Support\Collection<class-string, \ReflectionClass<object>>  $reflections
     * @return \Illuminate\Support\Collection<class-string<\Tooling\Composer\ClassMap\Collectors\Contracts\Collector>, array<int, class-string>>
     */
    private function classify(Collection $reflections): Collection
    {
        return collect(app()->tagged('tooling.classmap.collectors'))->mapWithKeys(
            fn (Collectors\Contracts\Collector $collector) => [
                $collector::class => $reflections->filter(
                    fn (ReflectionClass $class) => $collector->collects($class, $reflections->all())
                )->keys()->all(),
            ]);
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
     * @param  \Illuminate\Support\Collection<class-string<\Tooling\Composer\ClassMap\Collectors\Contracts\Collector>, array<int, class-string>>  $data
     */
    private function write(Collection $data): true
    {
        // Same folder keeps both files on one filesystem; across filesystems, rename() falls back to a non-atomic copy.
        $temporaryPath = $this->cachePath.'.'.Str::random(16).'.tmp';

        $this->files->ensureDirectoryExists(dirname($this->cachePath));

        try {
            $successful = $this->files->put($temporaryPath, $this->export($data)) !== false
                && $this->files->move($temporaryPath, $this->cachePath);
        } finally {
            $this->files->delete($temporaryPath);
        }

        throw_unless($successful, RuntimeException::class, sprintf('Could not write the class map cache to [%s].', $this->cachePath));

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->cachePath, true);
        }

        return true;
    }

    /**
     * @param  \Illuminate\Support\Collection<class-string<\Tooling\Composer\ClassMap\Collectors\Contracts\Collector>, array<int, class-string>>  $data
     */
    private function export(Collection $data): string
    {
        $collectors = $data->map(fn (array $classes, string $collector): string => sprintf(
            '    %s => %s,',
            var_export($collector, true),
            match ($classes) {
                [] => '[]',
                default => "[\n".collect($classes)->map(fn (string $class): string => '        '.var_export($class, true).',')->implode("\n")."\n    ]",
            },
        ));

        return "<?php\n\nreturn [\n".$collectors->implode("\n")."\n];\n";
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
        return match (true) {
            $this->hasCache($key) && $this->cacheFileIsNewer($key) => true,
            $this->hasCache($key) => $this->composer->hasSourcePsr4ChangedSince($this->cacheTime($key)),
            ! $this->files->exists($this->cachePath) => true,
            default => $this->composer->hasSourcePsr4ChangedSince($this->files->lastModified($this->cachePath)),
        };
    }

    private function cacheFileIsNewer(string $key): bool
    {
        return $this->files->exists($this->cachePath) && $this->files->lastModified($this->cachePath) > $this->cacheTime($key);
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
