<?php

declare(strict_types=1);

// @php-cs-fixer-ignore php_unit_method_casing

namespace Tests\Fixtures\Tooling;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TestCaseWithTestAttribute extends TestCase
{
    #[Test]
    public function testAlreadyAttributed(): void {}
}
