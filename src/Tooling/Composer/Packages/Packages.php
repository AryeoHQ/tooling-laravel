<?php

declare(strict_types=1);

namespace Tooling\Composer\Packages;

use BadMethodCallException;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\SplFileInfo;

use function Illuminate\Filesystem\join_paths;

/**
 * @mixin Collection<array-key, Package>
 */
class Packages
{
    public readonly false|string $vendorDirectory;

    public null|string $composerDirectory {
        get => $this->composerDirectory ??= when(
            $this->vendorDirectory,
            fn ($directory) => join_paths($directory, 'composer')
        );
    }

    public null|SplFileInfo $installedManifestFile {
        get => $this->installedManifestFile ??= when(
            $this->composerDirectory,
            fn ($directory) => new SplFileInfo($path = join_paths($directory, 'installed.json'), '', basename($path)),
        );
    }

    /** @var array<array-key, mixed> */
    public array $installed {
        get => $this->installed ??=
            $this->installedManifestFile
                ? data_get(json_decode($this->installedManifestFile->getContents()), 'packages', [])
                : [];
    }

    /** @var Collection<array-key, Package> */
    protected Collection $proxy {
        get => $this->proxy ??= collect($this->installed)->mapInto(Package::class);
    }

    public function __construct(string $vendorDirectory)
    {
        $this->vendorDirectory = realpath($vendorDirectory);
    }

    public static function make(string $vendorDirectory): static
    {
        return resolve(static::class, ['vendorDirectory' => $vendorDirectory]);
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
}
