<?php

declare(strict_types=1);

namespace Tooling\Rector\Support\Nodes;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

final class Method
{
    public string $of;

    public string $name;

    /** @var array<array-key, string> */
    protected array $aliases = [];

    public null|string $description;

    private null|ReflectionMethod $reflection {
        get => rescue(fn (): ReflectionMethod => new ReflectionMethod($this->of, $this->name), null, false);
    }

    public null|string $type {
        get => $this->type ?? match ($this->reflection?->hasReturnType()) {
            true => match ($this->reflection->getReturnType()::class) {
                ReflectionNamedType::class => $this->reflection->getReturnType()->getName(),
                default => throw new LogicException('Union and Intersection types are not supported yet.'),
            },
            default => null,
        };
    }

    public bool $isStatic {
        get => $this->reflection?->isStatic() ?? false;
    }

    public bool $allowsNull {
        get => $this->reflection?->getReturnType()?->allowsNull() ?? false;
    }

    /** @var array<array-key, string> */
    private array $aliasable = ['name', 'type'];

    /** @var Collection<array-key, Parameter> */
    private Collection $parameters {
        get => collect($this->reflection?->getParameters())->map(
            fn (ReflectionParameter $parameter): Parameter => new Parameter($this, $parameter->getName())
        );
    }

    public function __construct(string $of, string $name, null|string $description = null)
    {
        $this->of = $of;
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * @param  string|array<array-key, string>  $key
     */
    public function alias(string|array $key, null|string $value = null): static
    {
        match (is_string($key)) {
            true => $this->addAlias($key, $value),
            false => collect($key)->each(fn ($value, $key) => $this->addAlias($key, $value)),
        };

        return $this;
    }

    private function addAlias(string $key, null|string $value): static
    {
        throw_unless(in_array($key, $this->aliasable, true), InvalidArgumentException::class, "Cannot alias [{$key}].");

        $this->aliases[$key] = $value;

        return $this;
    }

    public function toDocBlockTag(): MethodTagValueNode
    {
        $type = data_get($this->aliases, 'type', $this->type);

        return new MethodTagValueNode(
            $this->isStatic,
            $type ? new IdentifierTypeNode("\\$type") : null,
            data_get($this->aliases, 'name', $this->name),
            $this->parameters->map->toDocBlockTag()->all(), /** @phpstan-ignore-line Rector vendored dependencies seem to be confusing PHPstan*/
            $this->description ?? '',
            []
        );
    }
}
