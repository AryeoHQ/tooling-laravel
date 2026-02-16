<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\PhpUnit;

class InvalidCamelCaseMethodTest extends \Tests\TestCase
{
    public function itDoesSomethingCool(): void {}
}
