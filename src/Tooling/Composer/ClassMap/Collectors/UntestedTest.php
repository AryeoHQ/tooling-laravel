<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\Fixtures\Tooling\Concern;
use Tests\Fixtures\Tooling\ParentClass;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Collectors\Provides\Fakeable;
use Tooling\Composer\ClassMap\Collectors\Provides\FakeableTestCases;

#[CoversClass(Untested::class)]
class UntestedTest extends TestCase
{
    use FakeableTestCases;

    #[Test]
    public function it_collects_a_class_without_a_test(): void
    {
        $collector = new Untested;

        $this->assertTrue($collector->collects(new ReflectionClass(ParentClass::class), []));
    }

    #[Test]
    public function it_skips_a_class_with_a_test(): void
    {
        $collector = new Untested;

        $this->assertFalse($collector->collects(new ReflectionClass(Untested::class), []));
    }

    #[Test]
    public function it_collects_a_trait_without_test_cases(): void
    {
        $collector = new Untested;

        $this->assertTrue($collector->collects(new ReflectionClass(Concern::class), []));
    }

    #[Test]
    public function it_skips_a_trait_with_test_cases(): void
    {
        $collector = new Untested;

        $this->assertFalse($collector->collects(new ReflectionClass(Fakeable::class), []));
    }

    #[Test]
    public function it_skips_test_classes(): void
    {
        $collector = new Untested;

        $this->assertFalse($collector->collects(new ReflectionClass(self::class), []));
    }

    #[Test]
    public function it_skips_test_case_traits(): void
    {
        $collector = new Untested;

        $this->assertFalse($collector->collects(new ReflectionClass(FakeableTestCases::class), []));
    }
}
