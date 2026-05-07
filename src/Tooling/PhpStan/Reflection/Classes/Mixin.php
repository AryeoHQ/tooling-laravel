<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Reflection\Classes;

use Illuminate\Support\Collection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ClosureType;
use Tooling\PhpStan\Reflection\Methods\Macro;

final class Mixin
{
    private ReflectionProvider $reflectionProvider;

    private string $class;

    /** @var Collection<string, Macro> */
    private Collection $methods {
        get => $this->methods ??= collect();
    }

    /**
     * @param  class-string  $class
     */
    public function __construct(ReflectionProvider $reflectionProvider, string $class)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->class = $class;
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName, bool $static = false): bool
    {
        return $this->getMethod($classReflection, $methodName, $static) instanceof Macro;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName, bool $static = false): null|Macro
    {
        $key = $classReflection->getName().'-'.$methodName.'-'.($static ? 'static' : 'instance');

        if ($this->methods->has($key)) {
            return $this->methods->get($key);
        }

        $macro = $this->lookup($classReflection, $methodName, $static);

        if ($macro instanceof Macro) {
            $this->methods->put($key, $macro);
        }

        return $macro;
    }

    private function lookup(ClassReflection $classReflection, string $methodName, bool $static): null|Macro
    {
        if (! $this->reflectionProvider->hasClass($this->class)) {
            return null;
        }

        $mixinReflection = $this->reflectionProvider->getClass($this->class);

        if (! $mixinReflection->hasNativeMethod($methodName)) {
            return null;
        }

        $returnType = $mixinReflection->getNativeMethod($methodName)->getVariants()[0]->getReturnType();

        if (! $returnType instanceof ClosureType) {
            return null;
        }

        return new Macro($classReflection, $methodName, $returnType, $static);
    }
}
