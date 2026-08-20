<?php

declare(strict_types=1);

// @php-cs-fixer-ignore php_unit_method_casing

namespace Tests\Fixtures\Tooling;

use PHPUnit\Framework\TestCase;

class TestCaseWithTestPrefixedMethods extends TestCase
{
    public function testCamelCase(): void {}

    public function test_snake_case(): void {}

    public function helper(): void {}

    protected function testProtected(): void {}

    public static function testStatic(): void {}
}
