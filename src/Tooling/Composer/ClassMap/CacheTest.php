<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\Fixtures\Tooling\ClassWithExtends;
use Tests\Fixtures\Tooling\Concern;
use Tests\Fixtures\Tooling\Contract;
use Tests\Fixtures\Tooling\FailingCollector;
use Tests\Fixtures\Tooling\ParentClass;
use Tests\Fixtures\Tooling\Unloadable;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Collectors\All;
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

        $this->assertIsArray($cache->get(Untested::class));

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
        ClassMapSource::fake()->merge([ParentClass::class => '/fake/src/ParentClass.php']);
        $cache = Cache::fake();
        $cache->build();

        $this->assertContains(ParentClass::class, $cache->get(Untested::class));
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
        $this->assertNotContains(Contract::class, $cache->get(Untested::class));

        Date::setTestNow(now()->addSecond());
        $classMapSource->merge([Contract::class => '/fake/src/Contract.php']);

        $this->assertContains(Contract::class, $cache->get(Untested::class));
    }

    #[Test]
    public function it_picks_up_new_files_on_rebuild(): void
    {
        $classMapSource = ClassMapSource::fake();
        $cache = Cache::fake();

        $cache->build();
        $this->assertNotContains(Concern::class, $cache->get(Untested::class));

        $classMapSource->merge([Concern::class => '/fake/src/Concern.php']);
        $cache->build();

        $this->assertContains(Concern::class, $cache->get(Untested::class));
    }

    #[Test]
    public function it_leaves_out_classes_that_cannot_load(): void
    {
        $unloadable = collect([
            Unloadable\MissingTrait::class,
            Unloadable\MissingParent::class,
            Unloadable\MissingInterface::class,
            Unloadable\ParentWithMissingTrait::class,
            Unloadable\ChildOfUnloadableParent::class,
            Unloadable\EnumWithMissingInterface::class,
        ]);

        ClassMapSource::fake()->merge([
            ClassWithExtends::class => '/fake/src/ClassWithExtends.php',
            ...$unloadable->mapWithKeys(fn (string $class) => [$class => '/fake/src/'.class_basename($class).'.php'])->all(),
        ]);
        $cache = Cache::fake();

        $cache->build();

        $this->assertContains(ClassWithExtends::class, $cache->get(All::class));
        $this->assertContains(ClassWithExtends::class, $cache->get(Untested::class));

        $this->assertEmpty($unloadable->intersect($cache->get(All::class))->all());
        $this->assertEmpty($unloadable->intersect($cache->get(Untested::class))->all());
    }

    #[Test]
    public function it_removes_the_fallback_autoloader_after_building(): void
    {
        $autoloaders = spl_autoload_functions();

        Cache::fake()->build();

        $this->assertSame($autoloaders, spl_autoload_functions());
    }

    #[Test]
    public function it_removes_the_fallback_autoloader_when_the_build_fails(): void
    {
        ClassMapSource::fake()->merge([ParentClass::class => '/fake/src/ParentClass.php']);
        $cache = Cache::fake();
        app()->tag(FailingCollector::class, 'tooling.classmap.collectors');
        $autoloaders = spl_autoload_functions();

        $this->assertThrows(fn () => $cache->build(), RuntimeException::class, 'failure');

        $this->assertSame($autoloaders, spl_autoload_functions());
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
