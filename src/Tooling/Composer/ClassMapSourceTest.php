<?php

declare(strict_types=1);

namespace Tooling\Composer;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClassMapSourceTest extends TestCase
{
    #[Test]
    public function it_is_registered_as_a_singleton(): void
    {
        $this->assertSame(resolve(ClassMapSource::class), resolve(ClassMapSource::class));
    }

    #[Test]
    public function it_matches_exclusion_wildcards_like_composer(): void
    {
        $classMap = ClassMapSource::fake([
            'App\ExampleShallow' => '/fake/src/ExampleShallow.php',
            'App\Nested\ExampleShallow' => '/fake/src/Nested/ExampleShallow.php',
            'App\Nested\ExampleDeep' => '/fake/src/Nested/ExampleDeep.php',
        ])->createMap('/fake/src', collect(['src/*Shallow.php', 'src/**/*Deep.php']), '/fake');

        $this->assertFalse($classMap->has('App\ExampleShallow'));
        $this->assertTrue($classMap->has('App\Nested\ExampleShallow'));
        $this->assertFalse($classMap->has('App\Nested\ExampleDeep'));
    }

    #[Test]
    public function it_resolves_current_directory_segments_in_exclusions(): void
    {
        $classMap = ClassMapSource::fake([
            'App\Excluded\Example' => '/fake/src/Excluded/Example.php',
        ])->createMap('/fake/src', collect(['./src/Excluded/']), '/fake');

        $this->assertTrue($classMap->has('App\Example'));
        $this->assertFalse($classMap->has('App\Excluded\Example'));
    }

    #[Test]
    public function it_resolves_parent_directory_segments_in_exclusions(): void
    {
        $classMap = ClassMapSource::fake([
            'App\Excluded\Example' => '/fake/src/Excluded/Example.php',
        ])->createMap('/fake/src', collect(['../src/Excluded/']), '/fake/app');

        $this->assertTrue($classMap->has('App\Example'));
        $this->assertFalse($classMap->has('App\Excluded\Example'));
    }
}
