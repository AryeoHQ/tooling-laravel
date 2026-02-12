<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PHPStan\PHPUnit;

use PHPUnit\Framework\Attributes\Test;

class MyTest extends \Tests\TestCase
{
    #[Test]
    public function it_does_something(): void {}
}
