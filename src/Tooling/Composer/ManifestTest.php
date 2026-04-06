<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\Packages\Package;

class ManifestTest extends TestCase
{
    #[Test]
    public function it_returns_packages_collection_with_current_package(): void
    {
        $manifest = Manifest::fake();

        tap($manifest->packages, function (Collection $packages) {
            $this->assertGreaterThan(0, $packages->count());
            $this->assertContainsOnlyInstancesOf(Package::class, $packages);
        });
    }

    #[Test]
    public function it_includes_current_package_in_packages(): void
    {
        $this->assertTrue(
            Manifest::fake()->packages->contains(
                resolve(Composer::class)->currentAsPackage
            )
        );
    }

    #[Test]
    public function it_returns_manifest_path(): void
    {
        $manifest = Manifest::fake();

        tap($manifest->manifestPath, function (string $path) {
            $this->assertStringContainsString('vendor', $path);
            $this->assertStringEndsWith('cache/configurations.php', $path);
        });
    }

    #[Test]
    public function it_builds_manifest_file(): void
    {
        $manifest = Manifest::fake();

        $this->assertTrue($manifest->build());
        $this->assertTrue(File::exists($manifest->manifestPath));
    }

    #[Test]
    public function it_contains_required_keys(): void
    {
        $manifest = Manifest::fake()->withRectorConfig()->withPhpStanConfig();

        $this->assertTrue($manifest->build());

        tap(File::getRequire($manifest->manifestPath), function (array $contents) {
            $this->assertArrayHasKey('rector', $contents);
            $this->assertArrayHasKey('phpstan', $contents);
        });
    }

    #[Test]
    public function it_returns_loaded_configuration(): void
    {
        $manifest = Manifest::fake()->withRectorConfig()->withPhpStanConfig();

        tap($manifest->loaded, function (object $loaded) {
            $this->assertIsObject($loaded);
            $this->assertObjectHasProperty('rector', $loaded);
            $this->assertObjectHasProperty('phpstan', $loaded);
        });
    }

    #[Test]
    public function it_returns_empty_configuration_when_no_tooling_config(): void
    {
        $manifest = Manifest::fake();

        tap($manifest->loaded, function (object $loaded) {
            $this->assertEmpty($loaded->rector);
            $this->assertEmpty($loaded->phpstan);
        });
    }

    #[Test]
    public function it_gets_data_with_get_method(): void
    {
        $manifest = Manifest::fake()->withRectorConfig()->withPhpStanConfig();

        $this->assertNotNull($manifest->get('rector'));
        $this->assertNotNull($manifest->get('phpstan'));
    }

    #[Test]
    public function it_gets_data_with_magic_get(): void
    {
        $manifest = Manifest::fake();

        $this->assertNotNull($manifest->rector);
        $this->assertNotNull($manifest->phpstan);
    }

    #[Test]
    public function it_returns_default_value_for_missing_keys(): void
    {
        $manifest = Manifest::fake();

        $missing = $manifest->get('nonexistent', 'default-value');

        $this->assertSame('default-value', $missing);
    }

    #[Test]
    public function it_returns_null_for_missing_keys_without_default(): void
    {
        $manifest = Manifest::fake();

        $missing = $manifest->get('nonexistent');

        $this->assertNull($missing);
    }

    #[Test]
    public function it_creates_cache_directory_if_not_exists(): void
    {
        $manifest = Manifest::fake();
        $manifest->build();

        $this->assertTrue(
            File::isDirectory(dirname($manifest->manifestPath))
        );
    }

    #[Test]
    public function it_returns_phpstan_configuration_as_array(): void
    {
        $manifest = Manifest::fake();

        $this->assertIsArray($manifest->get('phpstan'));
    }

    #[Test]
    public function it_returns_rector_configuration_as_array(): void
    {
        $manifest = Manifest::fake();

        $this->assertIsArray($manifest->get('rector'));
    }

    #[Test]
    public function it_includes_skip_in_rector_configuration(): void
    {
        $manifest = Manifest::fake()->withRectorConfig();

        tap($manifest->get('rector'), function (array $rector) {
            $this->assertArrayHasKey('skip', $rector);
            $this->assertIsArray($rector['skip']);
            $this->assertArrayHasKey('FakeSkipRule', $rector['skip']);
            $this->assertSame(['path/to/file.php'], $rector['skip']['FakeSkipRule']);
        });
    }

    #[Test]
    public function it_picks_up_new_config_on_rebuild(): void
    {
        $manifest = Manifest::fake();

        // Initial build — no tooling config
        $manifest->build();
        $this->assertEmpty($manifest->get('rector'));

        // Add rector config
        $manifest->withRectorConfig();

        // Rebuild should pick up the new config
        $manifest->build();
        $this->assertNotEmpty($manifest->get('rector'));
    }
}
