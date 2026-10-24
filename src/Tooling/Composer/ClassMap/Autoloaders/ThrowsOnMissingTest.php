<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Autoloaders;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\Fixtures\Tooling\Unloadable;
use Tests\TestCase;
use Throwable;
use Tooling\Composer\ClassMap\Autoloaders\Exceptions\ClassNotFound;

#[CoversClass(ThrowsOnMissing::class)]
class ThrowsOnMissingTest extends TestCase
{
    #[Test]
    public function it_lets_existence_checks_return_false(): void
    {
        spl_autoload_register($autoloader = new ThrowsOnMissing);

        $exists = rescue(fn () => class_exists(Unloadable\Missing::class), report: false);

        spl_autoload_unregister($autoloader);

        $this->assertFalse($exists);
    }

    #[Test]
    public function it_throws_when_a_class_needs_something_missing(): void
    {
        spl_autoload_register($autoloader = new ThrowsOnMissing);

        $exception = rescue(
            fn () => new ReflectionClass(Unloadable\MissingParent::class),
            fn (Throwable $exception) => $exception,
            report: false,
        );

        spl_autoload_unregister($autoloader);

        $this->assertInstanceOf(ClassNotFound::class, $exception);
        $this->assertSame('Class "Tests\Fixtures\Tooling\Unloadable\Missing" not found.', $exception->getMessage());
    }

    #[Test]
    public function it_throws_when_an_existence_check_loads_a_class_that_needs_something_missing(): void
    {
        spl_autoload_register($autoloader = new ThrowsOnMissing);

        $exception = rescue(
            fn () => class_exists(Unloadable\MissingTrait::class),
            fn (Throwable $exception) => $exception,
            report: false,
        );

        spl_autoload_unregister($autoloader);

        $this->assertInstanceOf(ClassNotFound::class, $exception);
        $this->assertSame('Class "Tests\Fixtures\Tooling\Unloadable\Missing" not found.', $exception->getMessage());
    }
}
