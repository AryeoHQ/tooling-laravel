<?php

declare(strict_types=1);

namespace Tests\Tooling\Composer;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Stringable;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\Composer\Packages\Package;
use Tooling\Composer\Packages\Packages;

class ComposerTest extends TestCase
{
    #[Test]
    public function it_is_registered_as_a_singleton(): void
    {
        $this->assertSame(app(Composer::class), app(Composer::class));
    }

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

    #[Test]
    public function it_returns_false_for_is_class_map_stale_after_fresh_optimize(): void
    {
        Process::run('composer dump -o --no-scripts --no-interaction');

        $composer = app(Composer::class);

        $this->assertFalse($composer->isClassMapStale);
    }

    #[Test]
    public function it_returns_true_for_is_class_map_stale_when_source_file_is_newer(): void
    {
        Process::run('composer dump -o --no-scripts --no-interaction');

        $composer = app(Composer::class);

        // Touch a source file so its mtime is newer than the classmap
        touch($composer->baseDirectory->toString().'/src/Tooling/Composer/Composer.php', time() + 10);

        $this->assertTrue($composer->isClassMapStale);
    }

    #[Test]
    public function it_returns_true_for_is_class_map_stale_when_classmap_references_deleted_file(): void
    {
        Process::run('composer dump -o --no-scripts --no-interaction');

        $composer = app(Composer::class);

        // Create a temporary PHP file in the source directory, re-dump, then delete it
        $tempFile = $composer->baseDirectory->toString().'/src/Tooling/Composer/TemporaryStaleTestClass.php';
        file_put_contents($tempFile, "<?php\n\nnamespace Tooling\\Composer;\n\nclass TemporaryStaleTestClass {}\n");

        Process::run('composer dump -o --no-scripts --no-interaction');

        // Re-resolve so classMap picks up the new file
        $composer = app(Composer::class);

        // Now delete the file — classmap still references it
        unlink($tempFile);

        $this->assertTrue($composer->isClassMapStale);
    }

    #[Test]
    public function it_optimize_class_map_produces_fresh_optimized_classmap(): void
    {
        Process::run('composer dump --no-scripts --no-interaction');

        $composer = app(Composer::class);

        $this->assertFalse($composer->isOptimized);

        $composer->optimizeClassMap();

        $this->assertTrue($composer->isOptimized);
        $this->assertInstanceOf(Collection::class, $composer->classMap);
        $this->assertNotEmpty($composer->classMap);
    }

    #[Test]
    public function it_optimize_class_map_throws_on_failure(): void
    {
        Process::run('composer dump --no-scripts --no-interaction');

        Process::fake([
            'composer dump-autoload -o --no-scripts --no-interaction' => Process::result(
                output: '',
                errorOutput: 'Something went wrong',
                exitCode: 1,
            ),
        ]);

        $composer = app(Composer::class);

        $this->assertFalse($composer->isOptimized);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to optimize classmap: Something went wrong');

        $composer->optimizeClassMap();
    }

    #[Test]
    public function it_optimize_class_map_optimizes_when_not_optimized(): void
    {
        Process::run('composer dump --no-scripts --no-interaction');

        $composer = app(Composer::class);

        $this->assertFalse($composer->isOptimized);

        $composer->optimizeClassMap();

        $this->assertTrue($composer->isOptimized);
    }

    #[Test]
    public function it_optimize_class_map_is_noop_when_fresh(): void
    {
        Process::run('composer dump -o --no-scripts --no-interaction');

        $composer = app(Composer::class);

        $this->assertTrue($composer->isOptimized);
        $this->assertFalse($composer->isClassMapStale);

        // Should not throw or run any process
        $composer->optimizeClassMap();

        $this->assertTrue($composer->isOptimized);
    }
}
