<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Symfony\Component\Finder\SplFileInfo;
use Tooling\Composer\Packages\Package;
use Tooling\Composer\Packages\Packages;

use function Illuminate\Filesystem\join_paths;

class Composer
{
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
        get => $this->classMap ??= collect((array) require $this->classMapFile->getRealPath());
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
            fn (string $path) => new Package(
                json_decode(new SplFileInfo($path, '', basename($path))->getContents())
            )
        );
    }

    public function vendorPath(string ...$path): string
    {
        return join_paths($this->vendorDirectory, ...$path);
    }
}
