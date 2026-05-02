<?php

declare(strict_types=1);

namespace Mcp\Servers;

use Illuminate\Support\Arr;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Server\Tool;
use Mcp\Servers\Registrar\Registrar;

class Server extends \Laravel\Mcp\Server
{
    public Registrar $registrar { get => resolve(Registrar::class); }

    /**
     * @param  class-string|array<class-string>  $primitives
     */
    final public static function add(string|array $primitives): void
    {
        collect(Arr::wrap($primitives))->each(
            fn (string $primitive): Registrar => resolve(Registrar::class)->register(static::class, $primitive)
        );
    }

    final protected function boot(): void
    {
        collect($this->registrar->for(static::class))->each(
            fn (string $primitive): null => $this->registerPrimitive($primitive)
        );
    }

    final protected function registerPrimitive(string $primitive): void
    {
        match (true) {
            is_subclass_of($primitive, Tool::class) => $this->tools[] = $primitive,
            is_subclass_of($primitive, Resource::class) => $this->resources[] = $primitive,
            is_subclass_of($primitive, Prompt::class) => $this->prompts[] = $primitive,
            default => throw new \InvalidArgumentException("Unsupported primitive type: {$primitive}"),
        };
    }
}
