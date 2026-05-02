<?php

declare(strict_types=1);

namespace Mcp\Servers\Registrar;

use Laravel\Mcp\Server\Primitive;
use Laravel\Mcp\Server\Tool;

class Registrar
{
    /** @var array<string, array<class-string<Tool>|Primitive>> */
    public private(set) array $registrations = [];

    public function register(string $server, string|Primitive $primitive): static
    {
        $this->registrations[$server][] = $primitive;

        return $this;
    }

    /**
     * @return array<array-key, class-string<Tool>|Primitive>
     */
    public function for(string $server): array
    {
        return $this->registrations[$server] ?? [];
    }
}
