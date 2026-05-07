<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Reflection\Methods;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ClosureType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Macro::class)]
final class MacroTest extends PHPStanTestCase
{
    private Macro $macro;

    private ClassReflection $classReflection;

    protected function setUp(): void
    {
        $reflectionProvider = self::createReflectionProvider();

        $this->classReflection = $reflectionProvider->getClass(\stdClass::class);

        $closureType = new ClosureType([], new StringType, false);

        $this->macro = new Macro($this->classReflection, 'testMethod', $closureType, false);
    }

    #[Test]
    public function it_returns_the_declaring_class(): void
    {
        $this->assertSame($this->classReflection, $this->macro->getDeclaringClass());
    }

    #[Test]
    public function it_returns_the_method_name(): void
    {
        $this->assertSame('testMethod', $this->macro->getName());
    }

    #[Test]
    public function it_is_public(): void
    {
        $this->assertTrue($this->macro->isPublic());
        $this->assertFalse($this->macro->isPrivate());
    }

    #[Test]
    public function it_is_not_static_by_default(): void
    {
        $this->assertFalse($this->macro->isStatic());
    }

    #[Test]
    public function it_can_be_static(): void
    {
        $closureType = new ClosureType([], new StringType, false);
        $macro = new Macro($this->classReflection, 'staticMethod', $closureType, true);

        $this->assertTrue($macro->isStatic());
    }

    #[Test]
    public function it_is_not_deprecated(): void
    {
        $this->assertSame(TrinaryLogic::createNo(), $this->macro->isDeprecated());
        $this->assertNull($this->macro->getDeprecatedDescription());
    }

    #[Test]
    public function it_is_not_final(): void
    {
        $this->assertSame(TrinaryLogic::createNo(), $this->macro->isFinal());
    }

    #[Test]
    public function it_is_not_internal(): void
    {
        $this->assertSame(TrinaryLogic::createNo(), $this->macro->isInternal());
    }

    #[Test]
    public function it_returns_itself_as_prototype(): void
    {
        $this->assertSame($this->macro, $this->macro->getPrototype());
    }

    #[Test]
    public function it_returns_variants_from_closure_type(): void
    {
        $variants = $this->macro->getVariants();

        $this->assertCount(1, $variants);
        $this->assertInstanceOf(StringType::class, $variants[0]->getReturnType());
    }

    #[Test]
    public function it_propagates_parameters_from_closure_type(): void
    {
        $closureType = new ClosureType([], new IntegerType, true);
        $macro = new Macro($this->classReflection, 'variadic', $closureType, false);

        $this->assertTrue($macro->getVariants()[0]->isVariadic());
        $this->assertInstanceOf(IntegerType::class, $macro->getVariants()[0]->getReturnType());
    }

    #[Test]
    public function it_has_no_doc_comment(): void
    {
        $this->assertNull($this->macro->getDocComment());
    }

    #[Test]
    public function it_has_no_throw_type(): void
    {
        $this->assertNull($this->macro->getThrowType());
    }

    #[Test]
    public function it_may_have_side_effects(): void
    {
        $this->assertSame(TrinaryLogic::createMaybe(), $this->macro->hasSideEffects());
    }
}
