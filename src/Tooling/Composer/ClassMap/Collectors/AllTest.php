<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Collectors\Provides\FakeableTestCases;

#[CoversClass(All::class)]
class AllTest extends TestCase
{
    use FakeableTestCases;

    #[Test]
    public function it_collects_every_class(): void
    {
        $collector = new All;

        $this->assertTrue($collector->collects(new ReflectionClass(All::class), []));
        $this->assertTrue($collector->collects(new ReflectionClass(self::class), []));
    }
}
