<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\PhpUnit;

class InvalidTestMethodPrefixTest extends \Tests\TestCase
{
    public function test_something(): void {}
}
