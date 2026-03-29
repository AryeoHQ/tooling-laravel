<?php

declare(strict_types=1);

namespace Tooling\Composer\Packages;

use BadMethodCallException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use IteratorAggregate;
use Traversable;

use function Illuminate\Filesystem\join_paths;

/**
 * @mixin Collection<array-key, Package>
 *
 * @implements IteratorAggregate<array-key, Package>
 */
class Packages implements IteratorAggregate
{
    public readonly string $vendorDirectory;

    private Filesystem $files;

    public null|string $composerDirectory {
        get => $this->composerDirectory ??= join_paths($this->vendorDirectory, 'composer');
    }

    public string $installedManifestPath {
        get => $this->installedManifestPath ??= join_paths($this->composerDirectory, 'installed.json');
    }

    /** @var array<array-key, mixed> */
    public array $installed {
        get => $this->installed ??= match ($this->files->exists($this->installedManifestPath)) {
            true => data_get(json_decode($this->files->get($this->installedManifestPath)), 'packages', []),
            false => [],
        };
    }

    /** @var Collection<array-key, Package> */
    protected Collection $proxy {
        get => $this->proxy ??= collect($this->installed)->mapInto(Package::class);
    }

    public function __construct(string $vendorDirectory, null|Filesystem $files = null)
    {
        $this->vendorDirectory = $vendorDirectory;
        $this->files = $files ?? new Filesystem;
    }

    public static function make(string $vendorDirectory, null|Filesystem $files = null): static
    {
        return resolve(static::class, ['vendorDirectory' => $vendorDirectory, 'files' => $files]);
    }

    private function isForwardableCall(string $method): bool
    {
        return $this->proxy::hasMacro($method) || method_exists($this->proxy, $method);
    }

    public function __call(string $method, array $arguments): mixed
    {
        $class = static::class;

        throw_unless($this->isForwardableCall($method), BadMethodCallException::class, "Call to undefined method {$class}::{$method}()");

        $result = $this->proxy->$method(...$arguments);

        return match ($result instanceof Collection) {
            true => match (rescue(fn () => $result->ensure(Package::class), fn () => false, false)) {
                true => $this,
                default => $result,
            },
            false => $result,
        };
    }

    public function getIterator(): Traversable
    {
        return $this->proxy->getIterator();
    }
}
