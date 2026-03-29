<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Collectors\Provides\FakeableTestCases;
use Tooling\Composer\Composer;

#[CoversClass(Untested::class)]
class UntestedTest extends TestCase
{
    use FakeableTestCases;

    #[Test]
    public function it_collects_classes_from_source_psr4_class_map(): void
    {
        $classes = resolve(Composer::class)->sourcePsr4ClassMap->keys();
        $collector = new Untested;

        $classes = $collector->collect($classes);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $classes);
        $this->assertNotEmpty($classes);
    }

    #[Test]
    public function it_excludes_test_classes_and_already_tested_classes(): void
    {
        $classes = resolve(Composer::class)->sourcePsr4ClassMap->keys();
        $collector = new Untested;

        $result = $collector->collect($classes);

        $this->assertFalse(
            $result->contains(fn (string $class) => str_ends_with($class, 'Test') || str_ends_with($class, 'TestCases')),
        );

        $result->each(function (string $class) use ($classes) {
            $this->assertFalse($classes->contains($class.'Test'), "{$class} has a co-located test and should be excluded");
        });
    }
}
