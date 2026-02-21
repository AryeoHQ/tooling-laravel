<?php

declare(strict_types=1);

namespace Tests\Tooling\Composer;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Stringable;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\Composer\Packages\Package;
use Tooling\Composer\Packages\Packages;

class ComposerTest extends TestCase
{
    #[Test]
    public function it_resolves_vendor_directory(): void
    {
        $composer = app(Composer::class);

        $this->assertIsString($composer->vendorDirectory);
        $this->assertDirectoryExists($composer->vendorDirectory);
        $this->assertStringEndsWith('vendor', $composer->vendorDirectory);
    }

    #[Test]
    public function it_returns_composer_json_file(): void
    {
        $composer = app(Composer::class);

        $this->assertInstanceOf(SplFileInfo::class, $composer->composerJsonFile);
        $this->assertSame('composer.json', $composer->composerJsonFile->getFilename());
        $this->assertFileExists($composer->composerJsonFile->getPathname());
    }

    #[Test]
    public function it_returns_classmap_file(): void
    {
        $composer = app(Composer::class);

        $this->assertInstanceOf(SplFileInfo::class, $composer->classMapFile);
        $this->assertSame('autoload_classmap.php', $composer->classMapFile->getFilename());
        $this->assertFileExists($composer->classMapFile->getPathname());
    }

    #[Test]
    public function it_returns_classmap(): void
    {
        $composer = app(Composer::class);

        $this->assertInstanceOf(Collection::class, $composer->classMap);
        $this->assertNotEmpty($composer->classMap);
    }

    #[Test]
    public function it_classmap_contains_class_to_file_mappings(): void
    {
        $composer = app(Composer::class);

        $composer->classMap->each(function ($filePath, $className) {
            $this->assertIsString($className);
            $this->assertIsString($filePath);
            $this->assertFileExists($filePath);
        });
    }

    #[Test]
    public function it_returns_packages_instance(): void
    {
        $composer = app(Composer::class);

        $this->assertInstanceOf(Packages::class, $composer->packages);
        $this->assertGreaterThan(0, $composer->packages->count());
        $this->assertNotNull($composer->packages->first()->name);
    }

    #[Test]
    public function it_returns_current_package(): void
    {
        $composer = app(Composer::class);

        $this->assertInstanceOf(Package::class, $composer->currentAsPackage);
        $this->assertInstanceOf(Stringable::class, $composer->currentAsPackage->name);
        $this->assertNotNull($composer->packages->first()->name);
    }

    #[Test]
    public function it_returns_self_package(): void
    {
        $composer = app(Composer::class);

        $this->assertInstanceOf(Package::class, $composer->selfAsPackage);
        $this->assertInstanceOf(Stringable::class, $composer->selfAsPackage->name);
        $this->assertSame('aryeo/tooling-laravel', $composer->selfAsPackage->name->toString());
    }

    #[Test]
    public function it_builds_vendor_path(): void
    {
        $composer = app(Composer::class);

        $path = $composer->vendorPath('composer', 'installed.json');

        $this->assertStringContainsString('vendor', $path);
        $this->assertStringEndsWith('composer/installed.json', $path);
        $this->assertFileExists($path);
    }

    #[Test]
    public function it_builds_vendor_path_with_multiple_segments(): void
    {
        $composer = app(Composer::class);

        $path = $composer->vendorPath('laravel', 'framework', 'src');

        $this->assertStringContainsString('vendor', $path);
        $this->assertStringEndsWith('laravel/framework/src', $path);
    }

    #[Test]
    public function it_builds_vendor_path_with_single_segment(): void
    {
        $composer = app(Composer::class);

        $path = $composer->vendorPath('autoload.php');

        $this->assertStringContainsString('vendor', $path);
        $this->assertStringEndsWith('vendor/autoload.php', $path);
        $this->assertFileExists($path);
    }

    #[Test]
    public function it_returns_false_for_is_optimized(): void
    {
        Process::run('composer dump --no-scripts --no-interaction');

        $composer = app(Composer::class);

        $this->assertFalse($composer->isOptimized);
    }

    #[Test]
    public function it_returns_true_for_is_optimized(): void
    {
        Process::run('composer dump -o --no-scripts --no-interaction');

        $composer = app(Composer::class);

        $this->assertTrue($composer->isOptimized);
    }
}
