<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Reflection\Classes;

use PHPStan\Testing\PHPStanTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\PhpStan\Reflection\MixesIn;
use Tooling\PhpStan\Reflection\Methods\Macro;

#[CoversClass(Mixin::class)]
final class MixinTest extends PHPStanTestCase
{
    private Mixin $mixin;

    protected function setUp(): void
    {
        $reflectionProvider = self::createReflectionProvider();

        $this->mixin = new Mixin($reflectionProvider, MixesIn::class);
    }

    #[Test]
    public function it_resolves_a_method_that_returns_a_closure(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(\stdClass::class);

        $this->assertTrue($this->mixin->hasMethod($classReflection, 'greet'));
    }

    #[Test]
    public function it_returns_a_macro_for_a_valid_method(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(\stdClass::class);

        $macro = $this->mixin->getMethod($classReflection, 'greet');

        $this->assertInstanceOf(Macro::class, $macro);
        $this->assertSame('greet', $macro->getName());
        $this->assertFalse($macro->isStatic());
    }

    #[Test]
    public function it_returns_null_for_a_method_that_does_not_return_a_closure(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(\stdClass::class);

        $this->assertFalse($this->mixin->hasMethod($classReflection, 'notAClosure'));
        $this->assertNull($this->mixin->getMethod($classReflection, 'notAClosure'));
    }

    #[Test]
    public function it_returns_null_for_a_method_that_does_not_exist(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(\stdClass::class);

        $this->assertFalse($this->mixin->hasMethod($classReflection, 'nonExistentMethod'));
        $this->assertNull($this->mixin->getMethod($classReflection, 'nonExistentMethod'));
    }

    #[Test]
    public function it_returns_null_when_mixin_class_does_not_exist(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(\stdClass::class);

        $mixin = new Mixin($reflectionProvider, 'NonExistent\\MixinClass'); // @phpstan-ignore argument.type

        $this->assertFalse($mixin->hasMethod($classReflection, 'greet'));
        $this->assertNull($mixin->getMethod($classReflection, 'greet'));
    }

    #[Test]
    public function it_caches_resolved_macros(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(\stdClass::class);

        $first = $this->mixin->getMethod($classReflection, 'greet');
        $second = $this->mixin->getMethod($classReflection, 'greet');

        $this->assertSame($first, $second);
    }

    #[Test]
    public function it_supports_static_methods(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(\stdClass::class);

        $macro = $this->mixin->getMethod($classReflection, 'count', static: true);

        $this->assertInstanceOf(Macro::class, $macro);
        $this->assertTrue($macro->isStatic());
    }

    #[Test]
    public function it_caches_static_and_non_static_separately(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass(\stdClass::class);

        $nonStatic = $this->mixin->getMethod($classReflection, 'greet');
        $static = $this->mixin->getMethod($classReflection, 'greet', static: true);

        $this->assertInstanceOf(Macro::class, $nonStatic);
        $this->assertInstanceOf(Macro::class, $static);
        $this->assertFalse($nonStatic->isStatic());
        $this->assertTrue($static->isStatic());
        $this->assertNotSame($nonStatic, $static);
    }
}
