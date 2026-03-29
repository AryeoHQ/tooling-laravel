<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Stringable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\Packages\Package;
use Tooling\Composer\Packages\Packages;

class ComposerTest extends TestCase
{
    #[Test]
    public function it_is_registered_as_a_singleton(): void
    {
        $this->assertSame(resolve(Composer::class), resolve(Composer::class));
    }

    #[Test]
    public function it_resolves_vendor_directory(): void
    {
        $composer = Composer::fake();

        $this->assertInstanceOf(\Illuminate\Support\Stringable::class, $composer->vendorDirectory);
        $this->assertTrue(File::exists($composer->vendorDirectory->toString()));
        $this->assertStringEndsWith('/vendor', $composer->vendorDirectory->toString());
    }

    #[Test]
    public function it_returns_composer_json_path(): void
    {
        $composer = Composer::fake();

        $this->assertIsString($composer->composerJsonPath);
        $this->assertStringEndsWith('/composer.json', $composer->composerJsonPath);
        $this->assertTrue(File::exists($composer->composerJsonPath));
    }

    #[Test]
    public function it_returns_packages_instance(): void
    {
        $composer = Composer::fake();

        $this->assertInstanceOf(Packages::class, $composer->packages);
        $this->assertGreaterThan(0, $composer->packages->count());
        $this->assertNotNull($composer->packages->first()->name);
    }

    #[Test]
    public function it_returns_current_package(): void
    {
        $composer = Composer::fake();

        $this->assertInstanceOf(Package::class, $composer->currentAsPackage);
        $this->assertInstanceOf(Stringable::class, $composer->currentAsPackage->name);
        $this->assertNotNull($composer->packages->first()->name);
    }

    #[Test]
    public function it_returns_self_package(): void
    {
        $composer = Composer::fake();

        $this->assertInstanceOf(Package::class, $composer->selfAsPackage);
        $this->assertInstanceOf(Stringable::class, $composer->selfAsPackage->name);
        $this->assertSame('aryeo/tooling-laravel', $composer->selfAsPackage->name->toString());
    }

    #[Test]
    public function it_builds_vendor_path(): void
    {
        $composer = Composer::fake();

        tap($composer->vendorPath('composer', 'installed.json'), function (string $path) {
            $this->assertStringContainsString('vendor', $path);
            $this->assertStringEndsWith('composer/installed.json', $path);
            $this->assertTrue(File::exists($path));
        });
    }

    #[Test]
    public function it_builds_vendor_path_with_multiple_segments(): void
    {
        $composer = Composer::fake();

        tap($composer->vendorPath('laravel', 'framework', 'src'), function (string $path) {
            $this->assertStringContainsString('vendor', $path);
            $this->assertStringEndsWith('laravel/framework/src', $path);
        });
    }

    #[Test]
    public function it_builds_vendor_path_with_single_segment(): void
    {
        $composer = Composer::fake();

        tap($composer->vendorPath('autoload.php'), function (string $path) {
            $this->assertStringContainsString('vendor', $path);
            $this->assertStringEndsWith('vendor/autoload.php', $path);
            $this->assertTrue(File::exists($path));
        });
    }

    #[Test]
    public function it_returns_source_psr4_class_map(): void
    {
        $composer = Composer::fake();

        tap($composer->sourcePsr4ClassMap, function ($classMap) {
            $this->assertNotEmpty($classMap);
            $this->assertTrue($classMap->has('App\\Example'));
            $this->assertTrue(File::exists($classMap->get('App\\Example')));
        });
    }

    #[Test]
    public function it_detects_source_psr4_changes_via_directory_mtimes(): void
    {
        $composer = Composer::fake();

        // A timestamp far in the future — no directory can be newer
        $this->assertFalse($composer->hasSourcePsr4ChangedSince(now()->timestamp + 3600));

        // A timestamp of 0 — every directory is newer than epoch
        $this->assertTrue($composer->hasSourcePsr4ChangedSince(0));
    }

    #[Test]
    public function it_automatically_detects_new_files_in_source_psr4_class_map(): void
    {
        $composer = Composer::fake();

        $this->assertTrue($composer->sourcePsr4ClassMap->has('App\Example'));

        $this->travel(1)->seconds();

        ClassMapSource::fake(['App\NewClass' => '/fake/src/NewClass.php']);

        $this->assertTrue($composer->sourcePsr4ClassMap->has('App\NewClass'));
    }
}
