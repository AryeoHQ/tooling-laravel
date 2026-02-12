<?php

declare(strict_types=1);

namespace Tests\Tooling\Composer\Packages;

use BadMethodCallException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\Composer\Packages\Package;
use Tooling\Composer\Packages\Packages;

class PackagesTest extends TestCase
{
    #[Test]
    public function it_resolves_vendor_directory(): void
    {
        $packages = new Packages(app(Composer::class)->vendorDirectory);

        $this->assertNotFalse($packages->vendorDirectory);
        $this->assertDirectoryExists($packages->vendorDirectory);
    }

    #[Test]
    public function it_returns_composer_directory(): void
    {
        $packages = new Packages(app(Composer::class)->vendorDirectory);

        $this->assertStringEndsWith('vendor/composer', $packages->composerDirectory);
        $this->assertDirectoryExists($packages->composerDirectory);
    }

    #[Test]
    public function it_returns_installed_manifest_file(): void
    {
        $packages = new Packages(app(Composer::class)->vendorDirectory);

        $this->assertInstanceOf(SplFileInfo::class, $packages->installedManifestFile);
        $this->assertSame('installed.json', $packages->installedManifestFile->getFilename());
        $this->assertFileExists($packages->installedManifestFile->getPathname());
    }

    #[Test]
    public function it_parses_installed_packages(): void
    {
        $packages = new Packages(app(Composer::class)->vendorDirectory);

        $this->assertIsArray($packages->installed);
        $this->assertNotEmpty($packages->installed);
        $this->assertIsObject($packages->installed[0]);
    }

    #[Test]
    public function it_creates_static_instance(): void
    {
        $packages = Packages::make(app(Composer::class)->vendorDirectory);

        $this->assertInstanceOf(Packages::class, $packages);
        $this->assertNotFalse($packages->vendorDirectory);
    }

    #[Test]
    public function it_forwards_collection_methods(): void
    {
        $packages = Packages::make(app(Composer::class)->vendorDirectory);

        $this->assertGreaterThan(0, $packages->count());
        $this->assertInstanceOf(Package::class, $packages->first());

        $filtered = $packages->filter(fn (Package $package) => $package->name?->contains('laravel'));

        $this->assertInstanceOf(Collection::class, $filtered);
        $this->assertContainsOnlyInstancesOf(Package::class, $filtered);
    }

    #[Test]
    public function it_returns_collection_for_mapped_results(): void
    {
        $packages = Packages::make(app(Composer::class)->vendorDirectory);

        $names = $packages->pluck('name');
        $this->assertInstanceOf(Collection::class, $names);
        $this->assertNotInstanceOf(Packages::class, $names);
    }

    #[Test]
    public function it_returns_scalar_values_from_collection_methods(): void
    {
        $packages = Packages::make(app(Composer::class)->vendorDirectory);

        $this->assertIsInt($packages->count());

        $this->assertIsBool($packages->isEmpty());
    }

    #[Test]
    public function it_throws_exception_for_invalid_methods(): void
    {
        $packages = Packages::make(app(Composer::class)->vendorDirectory);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Tooling\Composer\Packages\Packages::nonExistentMethod()');

        $packages->nonExistentMethod(); // @phpstan-ignore-line
    }

    #[Test]
    public function it_handles_invalid_vendor_directory(): void
    {
        $packages = new Packages('/path/that/does/not/exist');

        $this->assertFalse($packages->vendorDirectory);
        $this->assertNull($packages->composerDirectory);
        $this->assertNull($packages->installedManifestFile);
        $this->assertEmpty($packages->installed);
    }

    #[Test]
    public function it_can_chain_collection_methods(): void
    {
        $packages = Packages::make(app(Composer::class)->vendorDirectory);

        $result = $packages->filter(fn (Package $package) => $package->name?->contains('symfony'))->take(5);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertLessThanOrEqual(5, $result->count());
    }

    #[Test]
    public function it_contains_package_objects(): void
    {
        $packages = Packages::make(app(Composer::class)->vendorDirectory);

        $this->assertContainsOnlyInstancesOf(Package::class, $packages);
    }
}
