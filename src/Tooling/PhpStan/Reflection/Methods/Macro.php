<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Reflection\Methods;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

final class Macro implements MethodReflection
{
    private ClassReflection $classReflection;

    private string $name;

    private ClosureType $closureType;

    private bool $static = false;

    public function __construct(ClassReflection $classReflection, string $name, ClosureType $closureType, bool $static = false)
    {
        $this->classReflection = $classReflection;
        $this->name = $name;
        $this->closureType = $closureType;
        $this->static = $static;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isStatic(): bool
    {
        return $this->static;
    }

    public function getDocComment(): string|null
    {
        return null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    /** {@inheritDoc} */
    public function getVariants(): array
    {
        return [
            new FunctionVariant(
                TemplateTypeMap::createEmpty(),
                null,
                $this->closureType->getParameters(),
                $this->closureType->isVariadic(),
                $this->closureType->getReturnType(),
            ),
        ];
    }

    public function getDeprecatedDescription(): string|null
    {
        return null;
    }

    public function getThrowType(): Type|null
    {
        return null;
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }
}
