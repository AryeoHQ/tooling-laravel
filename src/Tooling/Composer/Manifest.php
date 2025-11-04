<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Support\Collection;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;
use Tooling\Composer\Packages\Package;

use function Illuminate\Filesystem\join_paths;

/**
 * @property-read null|stdClass $phpstan
 * @property-read null|stdClass $rector
 */
final class Manifest
{
    public Composer $composer { get => $this->composer ??= new Composer; }

    /** @var Collection<array-key, Package> */
    public Collection $packages {
        get => $this->packages ??= $this->composer->packages->push(
            $this->composer->currentAsPackage
        );
    }

    public Filesystem $files { get => $this->files ??= new Filesystem; }

    public string $manifestPath {
        get => join_paths($this->composer->vendorDirectory, $this->composer->selfAsPackage->name->toString(), 'cache/configurations.php');
    }

    public object $loaded {
        get => $this->loaded ??= when($this->build(), fn (): object => (object) require $this->manifestPath, new stdClass);
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
                    array_merge_recursive($carry->get($key, []), require $value)
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

        return collect($configuration)->map(
            fn (string $path): string => match ($package->name === $this->composer->currentAsPackage->name) {
                true => join_paths($this->composer->baseDirectory->toString(), $path),
                false => join_paths($this->composer->baseDirectory->toString(), 'vendor', $package->name->toString(), $path)
            }
        )->filter(
            fn ($path) => is_file($path)
        );
    }

    /**
     * @return Collection<array-key, null|string>
     */
    private function collectPhpStan(): Collection
    {
        return $this->packages->map(
            fn (Package $package): null|string => $this->extractPHPStan($package)
        )->flatten()->filter()->unique()->values();
    }

    private function extractPHPStan(Package $package): null|string
    {
        $configuration = data_get($package, 'extra.tooling.phpstan');

        if (! $configuration) {
            return null;
        }

        $path = match ($package->name === $this->composer->currentAsPackage->name) {
            true => join_paths($this->composer->baseDirectory->toString(), $configuration),
            false => join_paths($this->composer->baseDirectory->toString(), 'vendor', $package->name->toString(), $configuration)
        };

        return is_file($path) ? $path : null;
    }

    /**
     * @param  array<array-key, mixed>  $manifest
     */
    private function write(array $manifest): void
    {
        $this->files->dumpFile($this->manifestPath, '<?php return '.var_export($manifest, true).';');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->loaded, $key, $default);
    }

    public function __get(string $key)
    {
        return $this->get($key);
    }
}
