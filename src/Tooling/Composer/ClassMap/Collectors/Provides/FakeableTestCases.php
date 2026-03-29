<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Collectors\Provides;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tooling\Composer\ClassMap\Collectors\Contracts\Collector;

trait FakeableTestCases
{
    protected Collector $collector {
        get => new ((new \ReflectionClass($this))->getAttributes(CoversClass::class)[0]->newInstance()->className());
    }

    #[Test]
    public function it_implements_collector_interface(): void
    {
        $this->assertInstanceOf(Collector::class, $this->collector);
    }

    #[Test]
    public function it_returns_an_array_when_faked(): void
    {
        $result = $this->collector::fake();

        $this->assertIsArray($result);
    }

    #[Test]
    public function it_returns_provided_classes_when_faked(): void
    {
        $result = $this->collector::fake([Model::class, Builder::class]);

        $this->assertSame([Model::class, Builder::class], $result);
    }
}
