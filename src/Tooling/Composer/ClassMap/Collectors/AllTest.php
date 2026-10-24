<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concern;
use Tests\Fixtures\Tooling\Contract;
use Tests\Fixtures\Tooling\EnumWithInterface;
use Tests\Fixtures\Tooling\ParentClass;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Cache;
use Tooling\Composer\ClassMap\Collectors\Provides\FakeableTestCases;
use Tooling\Composer\ClassMapSource;

#[CoversClass(All::class)]
class AllTest extends TestCase
{
    use FakeableTestCases;

    #[Test]
    public function it_collects_every_class_that_loads(): void
    {
        $classes = collect([ParentClass::class, Contract::class, Concern::class, EnumWithInterface::class, self::class]);
        ClassMapSource::fake()->merge($classes->mapWithKeys(fn (string $class) => [$class => '/fake/src/'.class_basename($class).'.php'])->all());
        $cache = Cache::fake();

        $cache->build();

        $this->assertEmpty($classes->diff($cache->get(All::class))->all());
    }
}
