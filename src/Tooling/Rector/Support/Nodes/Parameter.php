<?php

declare(strict_types=1);

namespace Tooling\Rector\Support\Nodes;

use LogicException;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFloatNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNullNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use ReflectionNamedType;
use ReflectionParameter;

final class Parameter
{
    public readonly Method $of;

    public readonly string $name;

    public null|string $type {
        get => $this->type ?? match ($this->reflection->hasType()) {
            true => match ($this->reflection->getType()::class) {
                ReflectionNamedType::class => $this->reflection->getType()->getName(),
                default => throw new LogicException('Union and Intersection types are not supported yet.'),
            },
            false => null,
        };
    }

    public bool $allowsNull {
        get => $this->allowsNull ??= $this->reflection?->getType()?->allowsNull() ?? false;
    }

    public bool $isPassedByReference {
        get => $this->isPassedByReference ??= $this->reflection?->isPassedByReference() ?? false;
    }

    public bool $isVariadic {
        get => $this->isVariadic ??= $this->reflection?->isVariadic() ?? false;
    }

    public bool $hasDefault {
        get => $this->hasDefault ??= $this->reflection?->isDefaultValueAvailable() ?? false;
    }

    public mixed $default {
        get => $this->default ??= $this->hasDefault ? $this->reflection?->getDefaultValue() : null;
    }

    private null|ReflectionParameter $reflection {
        get => $this->reflection ??= rescue(fn (): ReflectionParameter => new ReflectionParameter([$this->of->of, $this->of->name], $this->name), null, false);
    }

    public function __construct(Method $of, null|string $name = null)
    {
        $this->of = $of;
        $this->name = $name;
    }

    public function toDocBlockTag(): MethodTagValueParameterNode
    {
        $type = match ((bool) $this->type) {
            true => match ($this->allowsNull) {
                true => new NullableTypeNode(new IdentifierTypeNode("\\{$this->type}")),
                false => new IdentifierTypeNode("\\{$this->type}"),
            },
            false => null
        };

        $default = match ($this->hasDefault) {
            true => match (gettype($this->default)) {
                'NULL' => new ConstExprNullNode,
                'boolean' => $this->default ? new ConstExprTrueNode : new ConstExprFalseNode,
                'integer' => new ConstExprIntegerNode((string) $this->default),
                'double' => new ConstExprFloatNode((string) $this->default),
                'float' => new ConstExprFloatNode((string) $this->default),
                'string' => new ConstExprStringNode($this->default, ConstExprStringNode::SINGLE_QUOTED),
                default => null,
            },
            false => null
        };

        return new MethodTagValueParameterNode(
            $type,
            $this->isPassedByReference,
            $this->isVariadic,
            '$'.$this->name,
            $default,
        );
    }
}
