<?php

declare(strict_types=1);

namespace Tests\Tooling\Composer;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\Composer\Manifest;
use Tooling\Composer\Packages\Package;

class ManifestTest extends TestCase
{
    #[Test]
    public function it_returns_composer_instance(): void
    {
        $manifest = new Manifest;

        $this->assertInstanceOf(Composer::class, $manifest->composer);
    }

    #[Test]
    public function it_returns_packages_collection_with_current_package(): void
    {
        $manifest = new Manifest;
        $packages = $manifest->packages;

        $this->assertInstanceOf(Collection::class, $packages);
        $this->assertGreaterThan(0, $packages->count());
        $this->assertContainsOnlyInstancesOf(Package::class, $packages);
    }

    #[Test]
    public function it_includes_current_package_in_packages(): void
    {
        $manifest = new Manifest;
        $packages = $manifest->packages;
        $currentPackage = $manifest->composer->currentAsPackage;

        $this->assertTrue($packages->contains($currentPackage));
    }

    #[Test]
    public function it_returns_filesystem_instance(): void
    {
        $manifest = new Manifest;

        $this->assertInstanceOf(Filesystem::class, $manifest->files);
    }

    #[Test]
    public function it_returns_manifest_path(): void
    {
        $manifest = new Manifest;
        $path = $manifest->manifestPath;

        $this->assertIsString($path);
        $this->assertStringContainsString('vendor', $path);
        $this->assertStringEndsWith('cache/configurations.php', $path);
    }

    #[Test]
    public function it_builds_manifest_file(): void
    {
        $manifest = new Manifest;
        $result = $manifest->build();

        $this->assertTrue($result);
        $this->assertFileExists($manifest->manifestPath);
    }

    #[Test]
    public function it_manifest_contains_required_keys(): void
    {
        $manifest = new Manifest;
        $manifest->build();

        $contents = require $manifest->manifestPath;

        $this->assertIsArray($contents);
        $this->assertArrayHasKey('rector', $contents);
        $this->assertArrayHasKey('phpstan', $contents);
    }

    #[Test]
    public function it_loads_manifest_after_building(): void
    {
        $manifest = new Manifest;
        $manifest->build();

        $loaded = $manifest->loaded;

        $this->assertIsObject($loaded);
        $this->assertObjectHasProperty('rector', $loaded);
        $this->assertObjectHasProperty('phpstan', $loaded);
    }

    #[Test]
    public function it_returns_empty_object_when_manifest_not_built(): void
    {
        $manifest = new Manifest;

        if (file_exists($manifest->manifestPath)) {
            unlink($manifest->manifestPath);
        }

        $loaded = $manifest->loaded;

        $this->assertInstanceOf(stdClass::class, $loaded);
    }

    #[Test]
    public function it_gets_data_with_get_method(): void
    {
        $manifest = new Manifest;
        $manifest->build();

        $rector = $manifest->get('rector');
        $phpstan = $manifest->get('phpstan');

        $this->assertNotNull($rector);
        $this->assertNotNull($phpstan);
    }

    #[Test]
    public function it_gets_data_with_magic_get(): void
    {
        $manifest = new Manifest;
        $manifest->build();

        $rector = $manifest->rector;
        $phpstan = $manifest->phpstan;

        $this->assertNotNull($rector);
        $this->assertNotNull($phpstan);
    }

    #[Test]
    public function it_returns_default_value_for_missing_keys(): void
    {
        $manifest = new Manifest;
        $manifest->build();

        $missing = $manifest->get('nonexistent', 'default-value');

        $this->assertSame('default-value', $missing);
    }

    #[Test]
    public function it_returns_null_for_missing_keys_without_default(): void
    {
        $manifest = new Manifest;
        $manifest->build();

        $missing = $manifest->get('nonexistent');

        $this->assertNull($missing);
    }

    #[Test]
    public function it_creates_cache_directory_if_not_exists(): void
    {
        $manifest = new Manifest;
        $manifest->build();

        $cacheDir = dirname($manifest->manifestPath);
        $this->assertDirectoryExists($cacheDir);
    }

    #[Test]
    public function it_returns_phpstan_configuration_as_array(): void
    {
        $manifest = new Manifest;
        $manifest->build();

        $phpstan = $manifest->get('phpstan');

        $this->assertTrue(is_array($phpstan));
    }

    #[Test]
    public function it_returns_rector_configuration_as_array(): void
    {
        $manifest = new Manifest;
        $manifest->build();

        $rector = $manifest->get('rector');

        $this->assertTrue(is_array($rector));
    }
}
