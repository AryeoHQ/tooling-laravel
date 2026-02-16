<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\PhpUnit;

use PHPUnit\Framework\Attributes\Test;

class ValidTestMethodNameTest extends \Tests\TestCase
{
    #[Test]
    public function it_does_something(): void {}
}
