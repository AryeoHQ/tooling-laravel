<?php

declare(strict_types=1);

namespace Tooling\Composer\Packages;

use BadMethodCallException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\Composer;

#[CoversClass(Packages::class)]
class PackagesTest extends TestCase
{
    #[Test]
    public function it_instantiates_with_make(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $this->assertInstanceOf(Packages::class, $packages);
    }

    #[Test]
    public function it_resolves_vendor_directory(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $this->assertSame($composer->vendorDirectory->toString(), $packages->vendorDirectory);
    }

    #[Test]
    public function it_returns_composer_directory(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $this->assertStringEndsWith('vendor/composer', $packages->composerDirectory);
    }

    #[Test]
    public function it_returns_installed_manifest_path(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $this->assertStringEndsWith('installed.json', $packages->installedManifestPath);
    }

    #[Test]
    public function it_parses_installed_packages(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $this->assertIsArray($packages->installed);
        $this->assertCount(1, $packages->installed);
        $this->assertIsObject($packages->installed[0]);
    }

    #[Test]
    public function it_forwards_collection_methods(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $this->assertSame(1, $packages->count());
        $this->assertInstanceOf(Package::class, $packages->first());

        $filtered = $packages->filter(fn (Package $package) => $package->name?->contains('test'));

        $this->assertInstanceOf(Collection::class, $filtered);
        $this->assertContainsOnlyInstancesOf(Package::class, $filtered);
    }

    #[Test]
    public function it_returns_collection_for_mapped_results(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $names = $packages->pluck('name');
        $this->assertInstanceOf(Collection::class, $names);
        $this->assertNotInstanceOf(Packages::class, $names);
    }

    #[Test]
    public function it_returns_scalar_values_from_collection_methods(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $this->assertIsInt($packages->count());
        $this->assertIsBool($packages->isEmpty());
    }

    #[Test]
    public function it_throws_exception_for_invalid_methods(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method '.Packages::class.'::nonExistentMethod()');

        $packages->nonExistentMethod(); // @phpstan-ignore method.notFound
    }

    #[Test]
    public function it_handles_invalid_vendor_directory(): void
    {
        $packages = new Packages('/path/that/does/not/exist');

        $this->assertSame('/path/that/does/not/exist', $packages->vendorDirectory);
        $this->assertEmpty($packages->installed);
    }

    #[Test]
    public function it_can_chain_collection_methods(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $result = $packages->filter(fn (Package $package) => $package->name?->contains('test'))->take(5);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    #[Test]
    public function it_contains_package_objects(): void
    {
        $composer = Composer::fake();

        $packages = Packages::make($composer->vendorDirectory->toString(), app('files'));

        $this->assertContainsOnlyInstancesOf(Package::class, $packages);
    }
}
