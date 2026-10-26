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
}
