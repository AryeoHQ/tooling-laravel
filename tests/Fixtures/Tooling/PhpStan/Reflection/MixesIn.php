<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Reflection;

use Closure;

final class MixesIn
{
    /** @return Closure(string $name): string */
    public function greet(): Closure
    {
        return fn (string $name): string => "Hello, {$name}!";
    }

    /** @return Closure(): int */
    public function count(): Closure
    {
        return fn (): int => 42;
    }

    public function notAClosure(): string
    {
        return 'not a closure';
    }
}
