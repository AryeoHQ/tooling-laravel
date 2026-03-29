<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Collectors\Untested;
use Tooling\Composer\ClassMapSource;
use Tooling\Composer\Composer;

class CacheTest extends TestCase
{
    #[Test]
    public function it_is_registered_as_a_singleton(): void
    {
        $this->assertSame(resolve(Cache::class), resolve(Cache::class));
    }

    #[Test]
    public function it_resolves_cache_path_under_vendor(): void
    {
        $cache = Cache::fake();

        $this->assertStringStartsWith(resolve(Composer::class)->vendorDirectory->toString(), $cache->cachePath);
        $this->assertStringEndsWith('cache/classmap.php', $cache->cachePath);
    }

    #[Test]
    public function it_returns_null_for_unknown_key(): void
    {
        $cache = Cache::fake();

        $this->assertNull($cache->get('nonexistent'));
    }

    #[Test]
    public function it_auto_builds_when_cache_file_does_not_exist(): void
    {
        $cache = Cache::fake();

        $this->assertFalse(File::exists($cache->cachePath));

        $cache->get(Untested::class);

        $this->assertTrue(File::exists($cache->cachePath));
    }

    #[Test]
    public function it_builds_cache_file(): void
    {
        $cache = Cache::fake();

        $this->assertTrue($cache->build());
        $this->assertTrue(File::exists($cache->cachePath));
        $this->assertArrayHasKey(Untested::class, $cache->loaded);
    }

    #[Test]
    public function it_returns_cached_data_after_build(): void
    {
        $cache = Cache::fake();
        $cache->build();
        $classes = $cache->get(Untested::class);

        $this->assertIsArray($classes);
        $this->assertNotEmpty($classes);
    }

    #[Test]
    public function it_returns_true_for_existing_cached_key(): void
    {
        $cache = Cache::fake();
        $cache->build();

        $this->assertTrue($cache->has(Untested::class));
    }

    #[Test]
    public function it_auto_rebuilds_when_a_source_directory_is_newer_than_cache(): void
    {
        $classMapSource = ClassMapSource::fake();
        $cache = Cache::fake();

        $cache->build();
        $this->assertNotContains('App\\NewClass', $cache->get(Untested::class));

        Date::setTestNow(now()->addSecond());
        $classMapSource->merge(['App\\NewClass' => '/fake/src/NewClass.php']);

        $this->assertContains('App\\NewClass', $cache->get(Untested::class));
    }

    #[Test]
    public function it_picks_up_new_files_on_rebuild(): void
    {
        $classMapSource = ClassMapSource::fake();
        $cache = Cache::fake();

        $cache->build();
        $this->assertNotContains('App\\RebuildTemp', $cache->get(Untested::class));

        $classMapSource->merge(['App\\RebuildTemp' => '/fake/src/RebuildTemp.php']);
        $cache->build();

        $this->assertContains('App\\RebuildTemp', $cache->get(Untested::class));
    }

    #[Test]
    public function it_does_not_rebuild_when_data_was_provided_via_fake(): void
    {
        $classMapSource = ClassMapSource::fake();
        $classMapSource->merge(['App\\NewClass' => '/fake/src/NewClass.php']);

        Untested::fake(['App\\SomeClass']);

        $cache = resolve(Cache::class);

        $this->assertSame(['App\\SomeClass'], $cache->get(Untested::class));
    }
}
