<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PHPStan\PHPUnit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MyTest extends TestCase
{
    #[Test]
    public function it_does_something_cool(): void {}
}
