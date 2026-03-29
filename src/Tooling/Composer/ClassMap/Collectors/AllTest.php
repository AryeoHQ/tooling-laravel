<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Collectors\Provides\FakeableTestCases;
use Tooling\Composer\Composer;

#[CoversClass(All::class)]
class AllTest extends TestCase
{
    use FakeableTestCases;

    #[Test]
    public function it_collects_all_classes_from_source_psr4_class_map(): void
    {
        $classes = Composer::fake()->sourcePsr4ClassMap->keys();
        $collector = new All;

        $result = $collector->collect($classes);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertNotEmpty($result);
        $this->assertCount($classes->count(), $result);
    }

    #[Test]
    public function it_returns_all_classes_without_filtering(): void
    {
        $classes = Composer::fake()->sourcePsr4ClassMap->keys();
        $collector = new All;

        $result = $collector->collect($classes);

        $this->assertEquals($classes->all(), $result->all());
    }
}
