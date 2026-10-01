<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Autoloaders;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\Fixtures\Tooling\Unloadable;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Autoloaders\Exceptions\ClassNotFound;

#[CoversClass(ThrowsOnMissing::class)]
class ThrowsOnMissingTest extends TestCase
{
    #[Test]
    public function it_lets_existence_checks_return_false(): void
    {
        $autoloader = new ThrowsOnMissing;
        spl_autoload_register($autoloader);

        try {
            $this->assertFalse(class_exists(Unloadable\Missing::class));
        } finally {
            spl_autoload_unregister($autoloader);
        }
    }

    #[Test]
    public function it_throws_when_a_class_needs_something_missing(): void
    {
        $autoloader = new ThrowsOnMissing;
        spl_autoload_register($autoloader);

        try {
            $this->assertThrows(
                fn () => new ReflectionClass(Unloadable\MissingParent::class),
                ClassNotFound::class,
                'Class "Tests\Fixtures\Tooling\Unloadable\Missing" not found.',
            );
        } finally {
            spl_autoload_unregister($autoloader);
        }
    }
}
